<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class CleanupExpiredOtp extends Command
{
    protected $signature = 'otp:cleanup';
    protected $description = 'Cleanup OTP codes that have expired or are older than 10 minutes';

    public function handle()
    {
        try {
            $expiredOtps = User::where('otp_expires_at', '<', Carbon::now())->get();

            if ($expiredOtps->isEmpty()) {
                $this->info('No expired OTPs found.');
                return;
            }

            foreach ($expiredOtps as $user) {
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->otp_requested_at = null;
                $user->save();
            }

            $this->info('Expired OTPs cleaned up successfully!');
        } catch (\Exception $e) {
            $this->error('Error during OTP cleanup: ' . $e->getMessage());
        }
    }
}
