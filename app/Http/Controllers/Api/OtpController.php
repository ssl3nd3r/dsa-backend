<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpMail;

class OtpController extends Controller
{
    /**
     * Resend OTP for registration
     */
    public function resendRegisterOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $email = $request->email;

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            return response()->json(['error' => 'User with this email already exists'], 400);
        }

        // Invalidate existing OTPs and generate new one
        Otp::where('email', $email)
            ->where('type', 'registration')
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $otp = Otp::generateOtp($email, 'registration');

        // Send OTP via email
        try {
            Mail::to($email)->send(new OtpMail($otp->otp_code, 'registration'));
            
            return response()->json([
                'message' => 'Registration OTP resent successfully',
                'email' => $email
            ]);
        } catch (\Exception $e) {
            // Delete the OTP if email sending fails
            $otp->delete();
            
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    /**
     * Resend OTP for login
     */
    public function resendLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $email = $request->email;

        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found with this email'], 404);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json(['error' => 'Account is deactivated'], 401);
        }

        // Invalidate existing OTPs and generate new one
        Otp::where('email', $email)
            ->where('type', 'login')
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $otp = Otp::generateOtp($email, 'login');

        // Send OTP via email
        try {
            Mail::to($email)->send(new OtpMail($otp->otp_code, 'login'));
            
            return response()->json([
                'message' => 'Login OTP resent successfully',
                'email' => $email
            ]);
        } catch (\Exception $e) {
            // Delete the OTP if email sending fails
            $otp->delete();
            
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }
}
