<?php

namespace Tests\Feature;

use App\Base\Constants\Auth\Role;
use App\Base\Exceptions\CustomValidationException;
use App\Base\Services\OTP\FirebasePhoneVerifier;
use App\Base\Services\OTP\Handler\OTPHandlerContract;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\UnencryptedToken;
use Mockery;
use Tests\TestCase;

/**
 * Firebase phone-auth OTP: the app sends a Firebase ID token (`firebase_id_token`) instead of
 * a server SMS `otp`. kreait's Auth::verifyIdToken is mocked (no call to Google); every other
 * check (provider, phone number, auth_time, single use, roles) runs for real.
 * Runs on the phpunit.xml sqlite :memory: connection with a minimal hand-built schema.
 */
class FirebasePhoneAuthTest extends TestCase
{
    /** @var array<string, UnencryptedToken> tokens the mocked verifier accepts, by string */
    private array $validTokens = [];

    private $firebaseAuth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName(), 'refusing to run outside sqlite');

        config([
            'auth.firebase_phone_auth.replay_cache_store' => 'array',
            'auth.firebase_phone_auth.project_id' => 'mercie-app',
            'auth.legacy_mobile_login_without_otp' => false,
        ]);
        Cache::store('array')->flush();

        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('username')->nullable();
            $t->string('email')->nullable();
            $t->string('mobile')->nullable();
            $t->string('password')->nullable();
            $t->unsignedInteger('country')->nullable();
            $t->boolean('active')->default(true);
            $t->string('fcm_token')->nullable();
            $t->string('login_by')->nullable();
            $t->string('lang')->nullable();
            $t->string('remember_token')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('roles', function (Blueprint $t) {
            $t->increments('id');
            $t->string('slug');
            $t->string('name')->nullable();
            $t->timestamps();
        });
        Schema::create('role_user', function (Blueprint $t) {
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('role_id');
        });
        Schema::create('countries', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('code')->nullable();
            $t->string('dial_code');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('drivers', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->string('mobile')->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('mobile_otp_verifications', function (Blueprint $t) {
            $t->increments('id');
            $t->string('mobile');
            $t->string('otp')->nullable();
            $t->boolean('verified')->default(false);
            $t->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        DB::table('countries')->insert(['id' => 1, 'name' => 'Cameroon', 'code' => 'CM', 'dial_code' => '+237']);
        DB::table('roles')->insert([
            ['id' => 1, 'slug' => Role::USER],
            ['id' => 2, 'slug' => Role::DRIVER],
            ['id' => 3, 'slug' => Role::SUPER_ADMIN],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Alice', 'mobile' => '690000000', 'country' => 1, 'password' => Hash::make('old-secret')],
            ['id' => 2, 'name' => 'Bob', 'mobile' => '691111111', 'country' => 1, 'password' => Hash::make('old-secret')],
            ['id' => 3, 'name' => 'Admin', 'mobile' => '692222222', 'country' => 1, 'password' => Hash::make('admin-secret')],
        ]);
        DB::table('role_user')->insert([
            ['user_id' => 1, 'role_id' => 1],
            ['user_id' => 2, 'role_id' => 2],
            ['user_id' => 3, 'role_id' => 3],
        ]);
        DB::table('drivers')->insert(['id' => 7, 'user_id' => 2, 'mobile' => '691111111']);

        $this->firebaseAuth = Mockery::mock(FirebaseAuth::class);
        $this->firebaseAuth->shouldReceive('verifyIdToken')->andReturnUsing(function ($idToken) {
            if (! isset($this->validTokens[$idToken])) {
                throw new FailedToVerifyToken('The token is invalid');
            }

            return $this->validTokens[$idToken];
        })->byDefault();
        $this->app->instance(FirebaseAuth::class, $this->firebaseAuth);
    }

    /**
     * Build a token the mocked Firebase verifier treats as validly signed.
     */
    private function firebaseToken(array $overrides = []): string
    {
        $claims = array_merge([
            'phone_number' => '+237690000000',
            'provider' => 'phone',
            'auth_time' => time() - 30,
            'sub' => 'firebase-uid-'.uniqid(),
            'aud' => 'mercie-app',
        ], $overrides);

        $builder = (new Builder(new JoseEncoder, ChainedFormatter::default()))
            ->issuedBy('https://securetoken.google.com/'.$claims['aud'])
            ->permittedFor($claims['aud'])
            ->relatedTo($claims['sub'])
            ->issuedAt(new DateTimeImmutable('-1 minute'))
            ->expiresAt(new DateTimeImmutable('+1 hour'))
            ->withClaim('auth_time', $claims['auth_time'])
            ->withClaim('firebase', ['sign_in_provider' => $claims['provider'], 'identities' => []]);

        if ($claims['phone_number'] !== null) {
            $builder = $builder->withClaim('phone_number', $claims['phone_number']);
        }

        $token = $builder->getToken(new Sha256, InMemory::plainText(str_repeat('k', 32)));
        $this->validTokens[$token->toString()] = $token;

        return $token->toString();
    }

    /**
     * The request was refused by validation (a 422; CustomValidationException currently renders
     * as a 500 because app/Exceptions/Handler.php is not registered in bootstrap/app.php).
     */
    private function assertRejected(TestResponse $response): void
    {
        $validationError = $response->status() === 422
            || $response->exception instanceof CustomValidationException
            || $response->exception instanceof ValidationException;

        $this->assertTrue($validationError, 'expected a validation rejection, got HTTP '.$response->status()
            .($response->exception ? ' '.get_class($response->exception).': '.$response->exception->getMessage() : ''));
        $this->assertNotTrue($response->json('success'));
        $this->assertNull($response->json('access_token'));
    }

    public function test_valid_token_logs_in_once_and_cannot_be_reused(): void
    {
        $token = $this->firebaseToken();

        $this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['access_token']);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
    }

    public function test_refreshed_token_from_same_sign_in_is_rejected(): void
    {
        $authTime = time() - 60;
        $first = $this->firebaseToken(['sub' => 'uid-1', 'auth_time' => $authTime]);
        $refreshed = $this->firebaseToken(['sub' => 'uid-1', 'auth_time' => $authTime]);
        $this->assertNotSame($first, $refreshed);

        $this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $first])->assertOk();
        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $refreshed]));
    }

    public function test_token_for_another_phone_is_rejected(): void
    {
        $token = $this->firebaseToken(['phone_number' => '+237699999999']);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_same_digits_with_other_dial_code_are_rejected(): void
    {
        // +33 690000000 is not the Cameroonian 690000000 stored on the account.
        $token = $this->firebaseToken(['phone_number' => '+33690000000']);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
    }

    public function test_old_auth_time_is_rejected(): void
    {
        $token = $this->firebaseToken(['auth_time' => time() - 11 * 60]);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
    }

    public function test_non_phone_provider_is_rejected(): void
    {
        $token = $this->firebaseToken(['provider' => 'password']);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
    }

    public function test_token_without_phone_number_is_rejected(): void
    {
        $token = $this->firebaseToken(['phone_number' => null]);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
    }

    public function test_token_for_another_firebase_project_is_rejected(): void
    {
        $token = $this->firebaseToken(['aud' => 'some-other-project']);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $token]));
    }

    public function test_unverifiable_token_is_rejected_and_not_logged(): void
    {
        Log::spy();
        $bogus = 'eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJ4In0.c2ln';

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'firebase_id_token' => $bogus]));

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context) use ($bogus) {
            return $message === 'Firebase phone verification failed'
                && ! str_contains(json_encode($context), $bogus);
        });
    }

    public function test_missing_otp_and_token_is_rejected(): void
    {
        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000']));
        $this->assertRejected($this->postJson('/api/v1/driver/login', ['mobile' => '691111111', 'role' => 'driver']));

        $this->assertRejected($this->postJson('/api/v1/user/update-password', ['mobile' => '690000000', 'password' => 'new-secret']));
        $this->assertRejected($this->postJson('/api/v1/driver/update-password', ['mobile' => '691111111', 'role' => 'driver', 'password' => 'new-secret']));

        $this->assertSame(0, DB::table('personal_access_tokens')->count());
        $this->assertTrue(Hash::check('old-secret', DB::table('users')->where('id', 1)->value('password')));
    }

    public function test_driver_login_with_valid_token(): void
    {
        $token = $this->firebaseToken(['phone_number' => '+237691111111']);

        $this->postJson('/api/v1/driver/login', ['mobile' => '691111111', 'role' => 'driver', 'firebase_id_token' => $token])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_update_password_with_token_works_once(): void
    {
        $token = $this->firebaseToken();

        $this->postJson('/api/v1/user/update-password', ['mobile' => '690000000', 'firebase_id_token' => $token, 'password' => 'new-secret'])
            ->assertOk();
        $this->assertTrue(Hash::check('new-secret', DB::table('users')->where('id', 1)->value('password')));

        $this->assertRejected($this->postJson('/api/v1/user/update-password', ['mobile' => '690000000', 'firebase_id_token' => $token, 'password' => 'another-one']));
        $this->assertTrue(Hash::check('new-secret', DB::table('users')->where('id', 1)->value('password')));
    }

    public function test_driver_update_password_with_token_for_wrong_phone_is_rejected(): void
    {
        $token = $this->firebaseToken(['phone_number' => '+237690000000']);

        $this->assertRejected($this->postJson('/api/v1/driver/update-password', ['mobile' => '691111111', 'role' => 'driver', 'firebase_id_token' => $token, 'password' => 'new-secret']));
        $this->assertTrue(Hash::check('old-secret', DB::table('users')->where('id', 2)->value('password')));

        $good = $this->firebaseToken(['phone_number' => '+237691111111']);
        $this->postJson('/api/v1/driver/update-password', ['mobile' => '691111111', 'role' => 'driver', 'firebase_id_token' => $good, 'password' => 'new-secret'])
            ->assertOk();
        $this->assertTrue(Hash::check('new-secret', DB::table('users')->where('id', 2)->value('password')));
    }

    public function test_token_cannot_reset_password_by_email(): void
    {
        DB::table('users')->where('id', 1)->update(['email' => 'alice@example.com']);
        $token = $this->firebaseToken();

        $this->assertRejected($this->postJson('/api/v1/user/update-password', ['email' => 'alice@example.com', 'firebase_id_token' => $token, 'password' => 'new-secret']));
        $this->assertTrue(Hash::check('old-secret', DB::table('users')->where('id', 1)->value('password')));
    }

    public function test_admin_role_cannot_log_in_with_firebase_token(): void
    {
        $token = $this->firebaseToken(['phone_number' => '+237692222222']);
        $this->firebaseAuth->shouldNotReceive('verifyIdToken');

        // Admin panel login path: passwordless login is never offered to admin roles.
        $controller = new class(app(User::class), app(OTPHandlerContract::class)) extends LoginController
        {
            public function attempt($request, $role)
            {
                return $this->loginUserAccount($request, $role, false);
            }
        };
        $request = Request::create('/admin-login', 'POST', ['mobile' => '692222222', 'firebase_id_token' => $token]);
        $response = $controller->attempt($request, [Role::SUPER_ADMIN]);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse(auth('web')->check());

        // The app endpoints only resolve app roles, so the admin's number is unknown there.
        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '692222222', 'firebase_id_token' => $token]));
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_server_otp_path_still_works(): void
    {
        DB::table('mobile_otp_verifications')->insert(['mobile' => '690000000', 'otp' => '482913', 'created_at' => now(), 'updated_at' => now()]);

        $this->assertRejected($this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'otp' => '111111']));
        $this->postJson('/api/v1/user/login', ['mobile' => '690000000', 'otp' => '482913'])->assertOk();
    }

    public function test_phone_matching_rules(): void
    {
        $verifier = new FirebasePhoneVerifier;

        $this->assertTrue($verifier->phoneMatches('+237690000000', '690000000', '+237'));
        $this->assertTrue($verifier->phoneMatches('+237690000000', '690 000 000', '237'));
        $this->assertTrue($verifier->phoneMatches('+237690000000', '+237690000000', '+237'));
        $this->assertTrue($verifier->phoneMatches('+237690000000', '237690000000', '+237'));
        $this->assertTrue($verifier->phoneMatches('+237690000000', '00237690000000', null));
        $this->assertFalse($verifier->phoneMatches('+237690000000', '690000001', '+237'));
        $this->assertFalse($verifier->phoneMatches('+237690000000', '690000000', null));
        $this->assertFalse($verifier->phoneMatches('+33690000000', '690000000', '+237'));
        $this->assertFalse($verifier->phoneMatches('', '690000000', '+237'));
    }
}
