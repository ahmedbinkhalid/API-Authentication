<?php

namespace App\Services;

use Carbon\Carbon;

class TokenService
{
    private $key;

    public function __construct()
    {
        $this->key = env('JWT_SECRET');  
    }

    // Generate a JWT token
    public function generateToken($user)
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $issuedAt = Carbon::now()->timestamp;
        $expirationTime = Carbon::now()->addHours(2)->timestamp;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    // Validate a JWT token
    public function validateToken($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $this->key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        if (!hash_equals($base64UrlSignature, $signatureProvided)) {
            return null; 
        }

        $payload = json_decode(base64_decode(strtr($payloadEncoded, '-_', '+/')), true);

        if (isset($payload['exp']) && Carbon::now()->timestamp > $payload['exp']) {
            return null; // Token expired
        }

        return $payload;
    }

    // Decode a JWT token (without verifying signature)
    public function decodeToken($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

        return json_decode(base64_decode(strtr($payloadEncoded, '-_', '+/')), true);
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
