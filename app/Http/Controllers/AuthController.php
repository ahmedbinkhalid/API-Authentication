<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Services\TokenService;

class AuthController extends Controller
{
    protected $tokenService;

    public function __construct(TokenService $tokenService){
        $this->tokenService = $tokenService;
    }
    // signup
    public function signup(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Mail::to($user->email)->send(new WelcomeMail($user));

            return response()->json([
                'message' => 'User registered successfully. A welcome email has been sent.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during signup: ' . $e->getMessage()
            ], 500);
        }
    }
    // Login
    public function login(Request $request){
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $this->tokenService->generateToken($user);

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Send otp
    public function sendOtp(Request $request){
        try {
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if ($user->otp_requested_at && now()->diffInSeconds($user->otp_requested_at) < 120) {
                $secondsLeft = 120 - now()->diffInSeconds($user->otp_requested_at);
                return response()->json([
                    'message' => 'Please wait before requesting a new OTP',
                    'retry_after_seconds' => $secondsLeft,
                ], 429);
            }
            $otp = random_int(1000, 9999);

            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10); 
            $user->otp_requested_at = now();               
            $user->save();

            Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Password Reset OTP');
            });

            return response()->json(['message' => 'OTP sent successfully'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while sending OTP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // verify and reset password
    public function resetPassword(Request $request){
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:4',
                'new_password' => 'required|min:6|confirmed',
            ]);

         
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

           
            if ($user->otp_code !== $validated['otp']) {
                return response()->json(['message' => 'Invalid OTP'], 400);
            }

            if (now()->gt($user->otp_expires_at)) {
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            
            $user->password = Hash::make($validated['new_password']);

           
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json([
                'message' => 'Password reset successful',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while resetting password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    


}

