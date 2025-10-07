<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckMembershipExpiry extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'membership:check-expiry';

    /**
     * The console command description.
     */
    protected $description = 'Check and deactivate expired memberships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking expired memberships...');

        $now = Carbon::now();
        
        // Find users with expired memberships that haven't been marked as expired yet
        $expiredUsers = User::where('membership_status', '!=', 'expired')
            ->where('membership_expires_at', '<=', $now)
            ->whereNotNull('membership_expires_at')
            ->get();

        $expiredCount = 0;

        foreach ($expiredUsers as $user) {
            try {
                // Update membership status to expired
                $user->update([
                    'membership_status' => 'expired',
                    'membership_expired_at' => $now
                ]);

                // Log the expiration
                Log::info('Membership expired for user', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'expired_at' => $now
                ]);

                $expiredCount++;
                $this->line("✓ Expired membership for: {$user->email}");

            } catch (\Exception $e) {
                Log::error('Failed to process expired membership', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                $this->error("✗ Failed to process {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Processed {$expiredCount} expired memberships");
    }
}