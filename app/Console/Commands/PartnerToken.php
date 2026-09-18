<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Partners\PartnerRegistry;
use Illuminate\Console\Command;

/**
 * Issues a Sanctum API token for an integration partner account (e.g. EstuaireAchats).
 * The token does not expire (config/sanctum.php expiration = null); revoke it with
 * --revoke-existing (same --name) when rotating.
 */
class PartnerToken extends Command
{
    protected $signature = 'partner:token
        {user : users.id of the partner account}
        {--name=partner-integration : token name}
        {--revoke-existing : delete the account\'s existing tokens with the same name first}';

    protected $description = 'Create a Sanctum API token for an integration partner account';

    public function handle(): int
    {
        $user = User::find($this->argument('user'));

        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if (! PartnerRegistry::isPartner($user->id)) {
            $this->warn("User {$user->id} is not listed in MERCI_E_PARTNER_USER_IDS: its requests will behave like a normal passenger's (auto-cancel, no webhooks).");
        }

        $name = (string) $this->option('name');

        if ($this->option('revoke-existing')) {
            $deleted = $user->tokens()->where('name', $name)->delete();
            $this->info("Revoked {$deleted} existing token(s) named \"{$name}\".");
        }

        $token = $user->createToken($name)->plainTextToken;

        $this->info("Token for user {$user->id} ({$user->name}). Shown once, store it in the partner system:");
        $this->line($token);

        return self::SUCCESS;
    }
}
