<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ServiceProviderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\OtpController;

// Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/register/complete', [UserController::class, 'completeRegister']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/login/complete', [UserController::class, 'completeLogin']);

// Register OTP routes
Route::post('/otp/register/resend', [OtpController::class, 'resendRegisterOtp']);

// Login OTP routes
Route::post('/otp/login/resend', [OtpController::class, 'resendLoginOtp']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    
    // Property routes
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/search', [PropertyController::class, 'search']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::put('/properties/{id}', [PropertyController::class, 'update']);
    Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
    Route::get('/properties/my/list', [PropertyController::class, 'myProperties']);
    
    // Message routes
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::put('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
    Route::put('/messages/user/{userId}/read-all', [MessageController::class, 'markAllAsRead']);
    Route::delete('/messages/{messageId}', [MessageController::class, 'destroy']);
    Route::get('/messages/property/{propertyId}', [MessageController::class, 'propertyMessages']);
    
    // Service Provider routes
    Route::get('/service-providers', [ServiceProviderController::class, 'index']);
    Route::get('/service-providers/search', [ServiceProviderController::class, 'search']);
    Route::get('/service-providers/types', [ServiceProviderController::class, 'serviceTypes']);
    Route::get('/service-providers/type/{serviceType}', [ServiceProviderController::class, 'byType']);
    Route::get('/service-providers/{id}', [ServiceProviderController::class, 'show']);
    Route::post('/service-providers', [ServiceProviderController::class, 'store']);
    Route::put('/service-providers/{id}', [ServiceProviderController::class, 'update']);
    Route::delete('/service-providers/{id}', [ServiceProviderController::class, 'destroy']);
    
    // Unified Review routes (for users, properties, and service providers)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{type}/{id}', [ReviewController::class, 'index']); // users/1, properties/1, service-providers/1
    Route::get('/reviews/my', [ReviewController::class, 'myReviews']);
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
    Route::get('/reviews/{type}/{id}/statistics', [ReviewController::class, 'statistics']);
    Route::get('/reviews/type/{type}', [ReviewController::class, 'byType']); // users, properties, service-providers
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'uptime' => time() - $_SERVER['REQUEST_TIME'] ?? time(),
    ]);
}); 