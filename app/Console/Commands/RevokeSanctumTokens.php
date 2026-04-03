<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class RevokeSanctumTokens extends Command
{
    protected $signature = 'sanctum:revoke-tokens {--all : Revoke every stored personal access token}';

    protected $description = 'Revoke Sanctum API tokens (forces clients to re-authenticate).';

    public function handle(): int
    {
        if (! $this->option('all')) {
            $this->error('Refusing to run without --all (destructive).');

            return self::FAILURE;
        }

        $deleted = PersonalAccessToken::query()->delete();
        $this->info("Revoked {$deleted} token(s).");

        return self::SUCCESS;
    }
}
