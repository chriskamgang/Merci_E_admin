<?php

namespace Tests\Feature;

use App\Jobs\KPayPollPendingTransactions;
use App\Models\Payment\DriverWallet;
use App\Models\Payment\PawaPayTransaction;
use App\Models\Payment\UserWallet;
use App\Models\User;
use App\Services\KPayService;
use App\Services\WalletService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Focused tests for the money-moving idempotency guarantees. Runs on the phpunit.xml
 * sqlite :memory: connection with a minimal hand-built schema (the full MySQL migration
 * set is not sqlite-compatible), never on the real database.
 */
class WalletConcurrencySafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName(), 'refusing to run outside sqlite');

        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('mobile')->nullable();
            $t->string('lang')->nullable();
            $t->unsignedInteger('country')->nullable();
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
        foreach (['user_wallet', 'driver_wallet'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->unsignedInteger('user_id');
                $t->double('amount_added')->default(0);
                $t->double('amount_balance')->default(0);
                $t->double('amount_spent')->default(0);
                $t->timestamps();
            });
        }
        foreach (['user_wallet_history', 'driver_wallet_history'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->unsignedInteger('user_id');
                $t->string('transaction_id')->nullable();
                $t->double('amount')->default(0);
                $t->string('remarks')->nullable();
                $t->boolean('is_credit')->default(false);
                $t->timestamps();
            });
        }
        Schema::create('pawapay_transactions', function (Blueprint $t) {
            $t->id();
            $t->string('transaction_id', 100)->unique();
            $t->string('external_id', 100)->nullable();
            $t->string('type');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('driver_id')->nullable();
            $t->decimal('amount', 12, 2);
            $t->string('currency', 10)->default('XAF');
            $t->string('phone', 30);
            $t->string('provider', 50);
            $t->string('status')->default('pending');
            $t->string('pawapay_status', 30)->nullable();
            $t->text('failure_reason')->nullable();
            $t->timestamps();
        });
        Schema::create('notification_channels', function (Blueprint $t) {
            $t->increments('id');
            $t->string('topics')->nullable();
            $t->boolean('push_notification')->default(false);
        });

        Schema::create('drivers', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();
        });

        DB::table('roles')->insert(['id' => 1, 'slug' => 'user']);
        DB::table('roles')->insert(['id' => 2, 'slug' => 'driver']);
        DB::table('users')->insert(['id' => 2, 'name' => 'Bob', 'mobile' => '691111111']);
        DB::table('role_user')->insert(['user_id' => 2, 'role_id' => 2]);
        DB::table('drivers')->insert(['id' => 7, 'user_id' => 2]);
        DB::table('users')->insert(['id' => 1, 'name' => 'Alice', 'mobile' => '690000000']);
        DB::table('role_user')->insert(['user_id' => 1, 'role_id' => 1]);
    }

    private function tx(array $attrs): PawaPayTransaction
    {
        return PawaPayTransaction::create(array_merge([
            'transaction_id' => 'T-'.uniqid(),
            'type' => 'deposit',
            'user_id' => 1,
            'amount' => 5000,
            'currency' => 'XAF',
            'phone' => '690000000',
            'provider' => 'MTN_MOMO_CMR',
            'status' => 'pending',
        ], $attrs));
    }

    public function test_deposit_is_credited_exactly_once(): void
    {
        $tx = $this->tx([]);

        $this->assertNotNull(WalletService::completeDeposit($tx));
        $this->assertNull(WalletService::completeDeposit($tx), 'second credit must be a no-op');
        $this->assertNull(WalletService::completeDeposit($tx->id), 'third credit must be a no-op');

        $this->assertEquals(5000, UserWallet::where('user_id', 1)->sum('amount_balance'));
        $this->assertSame(1, DB::table('user_wallet_history')->count());
        $this->assertSame('completed', $tx->fresh()->status);
    }

    public function test_failed_deposit_is_never_credited(): void
    {
        $tx = $this->tx([]);

        $this->assertTrue(WalletService::failTransaction($tx, 'FAILED', 'nope'));
        $this->assertNull(WalletService::completeDeposit($tx));
        $this->assertEquals(0, UserWallet::where('user_id', 1)->sum('amount_balance'));
    }

    public function test_failed_payout_is_refunded_exactly_once(): void
    {
        DriverWallet::create(['user_id' => 7, 'amount_added' => 0, 'amount_balance' => 1000, 'amount_spent' => 0]);
        $tx = $this->tx(['type' => 'payout', 'driver_id' => 7, 'amount' => 3000]);

        $this->assertTrue(WalletService::failTransaction($tx, 'FAILED'));
        $this->assertFalse(WalletService::failTransaction($tx, 'FAILED'), 'double refund must be a no-op');
        $this->assertFalse(WalletService::completePayout($tx), 'failed payout cannot become completed');

        $this->assertEquals(4000, DriverWallet::where('user_id', 7)->value('amount_balance'));
        $this->assertSame(1, DB::table('driver_wallet_history')->count());
    }

    public function test_completed_payout_cannot_be_refunded(): void
    {
        DriverWallet::create(['user_id' => 7, 'amount_added' => 0, 'amount_balance' => 0, 'amount_spent' => 0]);
        $tx = $this->tx(['type' => 'payout', 'driver_id' => 7, 'amount' => 3000]);

        $this->assertTrue(WalletService::completePayout($tx));
        $this->assertFalse(WalletService::completePayout($tx));
        $this->assertFalse(WalletService::failTransaction($tx, 'FAILED'));
        $this->assertEquals(0, DriverWallet::where('user_id', 7)->value('amount_balance'));
    }

    public function test_poll_job_ignores_gfsolutions_rows_and_credits_once(): void
    {
        $kpayTx = $this->tx(['transaction_id' => 'KPAY-1']);
        $gfsTx = $this->tx(['transaction_id' => 'GFS-1', 'provider' => 'GFSOLUTIONS']);

        $kpay = $this->createMock(KPayService::class);
        $kpay->method('isConfigured')->willReturn(true);
        $kpay->expects($this->exactly(1))
            ->method('checkDepositStatus')
            ->with('KPAY-1')
            ->willReturn(['status' => 'COMPLETED']);

        (new KPayPollPendingTransactions)->handle($kpay);
        // A second run (e.g. scheduler + dispatched job) finds nothing pending.
        (new KPayPollPendingTransactions)->handle($kpay);

        $this->assertSame('completed', $kpayTx->fresh()->status);
        $this->assertSame('pending', $gfsTx->fresh()->status);
        $this->assertEquals(5000, UserWallet::where('user_id', 1)->sum('amount_balance'));
    }

    public function test_transfer_and_points_reject_zero_or_negative_amounts(): void
    {
        $user = User::find(1);

        foreach ([0, -100, 'abc', 10.5] as $amount) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/payment/wallet/transfer-money-from-wallet', ['mobile' => '691111111', 'role' => 'user', 'amount' => $amount])
                ->assertStatus(422);
        }
        foreach ([0, -5, 'abc'] as $amount) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/payment/wallet/convert-point-to-wallet', ['amount' => $amount])
                ->assertStatus(422);
        }
    }

    public function test_kpay_rejects_invalid_amounts(): void
    {
        $user = User::find(1);
        $kpay = $this->createMock(KPayService::class);
        $kpay->expects($this->never())->method('initiateWithdrawal');
        $kpay->expects($this->never())->method('initiateDeposit');
        $this->app->instance(KPayService::class, $kpay);

        foreach ([0, -100, 99, 150.5, 'abc'] as $amount) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/payment/kpay/withdraw', ['amount' => $amount, 'phone' => '690000000', 'provider' => 'MTN_MOMO_CMR'])
                ->assertStatus(422);
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/payment/kpay/deposit', ['amount' => $amount, 'phone' => '690000000', 'provider' => 'MTN_MOMO_CMR'])
                ->assertStatus(422);
        }
    }

    public function test_withdrawal_debits_once_blocks_parallel_and_refunds_immediate_failure_once(): void
    {
        DriverWallet::create(['user_id' => 7, 'amount_added' => 0, 'amount_balance' => 5000, 'amount_spent' => 0]);
        $driverUser = User::find(2);
        $body = ['amount' => '2000', 'phone' => '690000000', 'provider' => 'MTN_MOMO_CMR'];

        $kpay = $this->createMock(KPayService::class);
        $kpay->method('isConfigured')->willReturn(true);
        $kpay->expects($this->exactly(2))->method('initiateWithdrawal')->willReturnOnConsecutiveCalls(
            ['status' => 'FAILED', 'failureReason' => 'x', '_http_status' => 200],
            ['id' => 'KPAY-W-1', 'status' => 'ACCEPTED', '_http_status' => 200],
        );
        $this->app->instance(KPayService::class, $kpay);

        // 1) immediate rejection -> refunded exactly once
        $this->actingAs($driverUser, 'sanctum')->postJson('/api/v1/payment/kpay/withdraw', $body)->assertStatus(422);
        $this->assertEquals(5000, DriverWallet::where('user_id', 7)->value('amount_balance'));
        $failed = PawaPayTransaction::where('type', 'payout')->first();
        $this->assertSame('failed', $failed->status);
        $this->assertFalse(WalletService::failTransaction($failed, 'FAILED'));
        $this->assertEquals(5000, DriverWallet::where('user_id', 7)->value('amount_balance'));

        // 2) accepted -> debited, row carries KPay id
        $this->actingAs($driverUser, 'sanctum')->postJson('/api/v1/payment/kpay/withdraw', $body)
            ->assertOk()->assertJson(['success' => true, 'withdrawal_id' => 'KPAY-W-1']);
        $this->assertEquals(3000, DriverWallet::where('user_id', 7)->value('amount_balance'));

        // 3) a second request while one is pending is refused before KPay is called
        $this->actingAs($driverUser, 'sanctum')->postJson('/api/v1/payment/kpay/withdraw', $body)
            ->assertJson(['success' => false]);
        $this->assertEquals(3000, DriverWallet::where('user_id', 7)->value('amount_balance'));

        // 4) insufficient balance is refused (after the pending one fails and is refunded)
        WalletService::failTransaction(PawaPayTransaction::where('transaction_id', 'KPAY-W-1')->first(), 'FAILED');
        $this->assertEquals(5000, DriverWallet::where('user_id', 7)->value('amount_balance'));
        $this->actingAs($driverUser, 'sanctum')->postJson('/api/v1/payment/kpay/withdraw', ['amount' => '6000'] + $body)
            ->assertJson(['success' => false]);
        $this->assertEquals(5000, DriverWallet::where('user_id', 7)->value('amount_balance'));
    }
}
