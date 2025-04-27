<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('otp:cleanup')->everyMinute();

// Schedule::call(function () {
//     try {
//         // Retrieve expired OTPs
//         $expiredOtps = User::where('otp_expires_at', '<', Carbon::now())->get();

//         // Check if any expired OTPs were found
//         if ($expiredOtps->isEmpty()) {
//             Log::info('No expired OTPs found.');
//             echo 'No expired OTPs found.';
//             return;
//         }

//         // Loop through each user with expired OTPs and reset their OTP data
//         foreach ($expiredOtps as $user) {
//             $user->otp_code = null;
//             $user->otp_expires_at = null;
//             $user->otp_requested_at = null;
//             $user->save();
//         }

//         // Output success message
//         echo 'Expired OTPs cleaned up successfully!';
//     } catch (\Exception $e) {
//         // Log the exception
//         Log::error('Error during OTP cleanup: ' . $e->getMessage());

//         // Output error message
//         echo 'An error occurred during OTP cleanup: ' . $e->getMessage();
//     }
// })->everyMinute();
// Schedule::call(function () {
//     $expiredOtps = User::where('otp_expires_at', '<', Carbon::now())->get();

//     foreach ($expiredOtps as $user) {
//         $user->otp_code = null;
//         $user->otp_expires_at = null;
//         $user->otp_requested_at = null;
//         $user->save();
//     }

//     echo 'Expired OTPs cleaned up successfully!';
// })->everyMinute();
