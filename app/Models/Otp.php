<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
        'is_used',
        'type'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Generate a new OTP for email verification
     */
    public static function generateOtp($email, $type = 'registration')
    {
        // Invalidate any existing unused OTPs for this email
        self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate new OTP
        $otp = self::create([
            'email' => $email,
            // 'otp_code' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'otp_code' => 123456,
            'expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
            'is_used' => false,
            'type' => $type
        ]);

        return $otp;
    }

    /**
     * Verify OTP code
     */
    public static function verifyOtp($email, $otpCode, $type = 'registration')
    {
        $otp = self::where('email', $email)
            ->where('otp_code', $otpCode)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->update(['is_used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if email has a valid unused OTP
     */
    public static function hasValidOtp($email, $type = 'registration')
    {
        return self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Check if email has been verified with OTP (has a used OTP within valid timeframe)
     */
    public static function isEmailVerified($email, $type = 'registration')
    {
        return self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', true)
            ->where('expires_at', '>', now()->subMinutes(30)) // Consider verified for 30 minutes after OTP expiry
            ->exists();
    }
} 