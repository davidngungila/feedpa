<?php

namespace App\Console\Commands;

use App\Models\UserSession;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-expired-sessions')]
#[Description('Cleanup expired user sessions')]
class CleanupExpiredSessions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeoutMinutes = 30;
        $expiredSessions = UserSession::where('last_activity', '<', now()->subMinutes($timeoutMinutes))->get();
        
        $this->info("Found {$expiredSessions->count()} expired session(s) to cleanup.");
        
        foreach ($expiredSessions as $session) {
            $session->delete();
            $this->line("Deleted expired session for user ID {$session->user_id}.");
        }
        
        $this->info('Session cleanup complete!');
    }
}
