<?php

namespace App\Console\Commands;

use App\Models\RefreshToken;
use Illuminate\Console\Command;

class PruneRefreshTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:prune-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune expired refresh tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Pruning expired refresh tokens...');

        $deletedCount = RefreshToken::pruneExpired();

        if ($deletedCount > 0) {
            $this->info("Successfully pruned {$deletedCount} expired refresh token(s).");
        } else {
            $this->info('No expired refresh tokens found.');
        }

        return Command::SUCCESS;
    }
}
