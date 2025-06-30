<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class UserController extends Controller
{
    // Register new user - Step 1: Validate data and send OTP
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Check if there's already a valid OTP for this email
        if (Otp::hasValidOtp($request->email, 'registration')) {
            return response()->json([
                'message' => 'Registration OTP already sent. Please check your email or request a new one.',
                'requires_otp' => true,
                'email' => $request->email
            ], 200);
        }

        // Generate and send registration OTP
        $otp = Otp::generateOtp($request->email, 'registration');

        try {
            Mail::to($request->email)->send(new OtpMail($otp->otp_code, 'registration'));
            
            return response()->json([
                'message' => 'Registration OTP sent successfully. Please check your email.',
                'requires_otp' => true,
                'email' => $request->email
            ]);
        } catch (\Exception $e) {
            // Delete the OTP if email sending fails
            $otp->delete();
            
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    // Complete registration with OTP - Step 2: Verify OTP and create user
    public function completeRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'lifestyle' => 'required|string',
            'personality_traits' => 'nullable|array|max:8',
            'work_schedule' => 'required|string',
            'cultural_preferences' => 'nullable|array',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Verify the OTP
        if (!Otp::verifyOtp($request->email, $request->otp_code, 'registration')) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'lifestyle' => $request->lifestyle,
            'personality_traits' => $request->personality_traits,
            'work_schedule' => $request->work_schedule,
            'cultural_preferences' => $request->cultural_preferences,
            'is_verified' => true, // User is verified since OTP was validated
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user->toPublicArray(),
        ], 201);
    }

    // Login user - Step 1: Validate credentials and send OTP
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->comparePassword($request->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        if (!$user->is_active) {
            return response()->json(['error' => 'Account is deactivated'], 401);
        }

        // Check if there's already a valid OTP for this email
        if (Otp::hasValidOtp($request->email, 'login')) {
            return response()->json([
                'message' => 'OTP already sent. Please check your email or request a new one.',
                'requires_otp' => true,
                'email' => $request->email
            ], 200);
        }

        // Generate and send login OTP
        $otp = Otp::generateOtp($request->email, 'login');

        try {
            Mail::to($request->email)->send(new OtpMail($otp->otp_code, 'login'));
            
            return response()->json([
                'message' => 'Login OTP sent successfully. Please check your email.',
                'requires_otp' => true,
                'email' => $request->email
            ]);
        } catch (\Exception $e) {
            // Delete the OTP if email sending fails
            $otp->delete();
            
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    // Complete login with OTP - Step 2: Verify OTP and generate token
    public function completeLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if (!$user->is_active) {
            return response()->json(['error' => 'Account is deactivated'], 401);
        }

        // Verify the OTP
        if (!Otp::verifyOtp($request->email, $request->otp_code, 'login')) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user->toPublicArray(),
        ]);
    }

    // Get current user profile
    public function profile(Request $request)
    {
        return response()->json(['user' => $request->user()->toPublicArray()]);
    }

    // Update user profile
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->only([
            'name', 'phone', 'lifestyle', 'personality_traits', 'work_schedule',
            'cultural_preferences', 'budget', 'preferred_areas', 'move_in_date', 'lease_duration'
        ]);
        $user->fill($data);
        $user->save();
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->toPublicArray(),
        ]);
    }
}
