<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceReview;
use App\Models\ServiceProvider;

class ServiceReviewController extends Controller
{
    // Create a new review
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'required|exists:service_providers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Check if user already reviewed this service provider
        $existingReview = ServiceReview::where('service_provider_id', $request->service_provider_id)
                                      ->where('user_id', $request->user()->id)
                                      ->first();

        if ($existingReview) {
            return response()->json(['error' => 'You have already reviewed this service provider'], 400);
        }

        $review = ServiceReview::create([
            'service_provider_id' => $request->service_provider_id,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified' => false,
        ]);

        // Update service provider's average rating
        $this->updateServiceProviderRating($request->service_provider_id);

        $review->load('user:id,name,profile_image');

        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review,
        ], 201);
    }

    // Get reviews for a service provider
    public function index(Request $request, $serviceProviderId)
    {
        $serviceProvider = ServiceProvider::find($serviceProviderId);
        
        if (!$serviceProvider) {
            return response()->json(['error' => 'Service provider not found'], 404);
        }

        $reviews = ServiceReview::where('service_provider_id', $serviceProviderId)
                               ->with('user:id,name,profile_image')
                               ->orderBy('created_at', 'desc')
                               ->paginate($request->get('per_page', 10));

        return response()->json([
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    // Get user's reviews
    public function myReviews(Request $request)
    {
        $reviews = ServiceReview::where('user_id', $request->user()->id)
                               ->with('serviceProvider:id,name,service_type')
                               ->orderBy('created_at', 'desc')
                               ->paginate($request->get('per_page', 10));

        return response()->json([
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    // Update a review
    public function update(Request $request, $reviewId)
    {
        $review = ServiceReview::where('id', $reviewId)
                              ->where('user_id', $request->user()->id)
                              ->first();

        if (!$review) {
            return response()->json(['error' => 'Review not found or access denied'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'integer|min:1|max:5',
            'comment' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $review->update($request->all());

        // Update service provider's average rating
        $this->updateServiceProviderRating($review->service_provider_id);

        $review->load('user:id,name,profile_image');

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review,
        ]);
    }

    // Delete a review
    public function destroy(Request $request, $reviewId)
    {
        $review = ServiceReview::where('id', $reviewId)
                              ->where('user_id', $request->user()->id)
                              ->first();

        if (!$review) {
            return response()->json(['error' => 'Review not found or access denied'], 404);
        }

        $serviceProviderId = $review->service_provider_id;
        $review->delete();

        // Update service provider's average rating
        $this->updateServiceProviderRating($serviceProviderId);

        return response()->json(['message' => 'Review deleted successfully']);
    }

    // Get review statistics for a service provider
    public function statistics($serviceProviderId)
    {
        $serviceProvider = ServiceProvider::find($serviceProviderId);
        
        if (!$serviceProvider) {
            return response()->json(['error' => 'Service provider not found'], 404);
        }

        $reviews = ServiceReview::where('service_provider_id', $serviceProviderId);
        
        $stats = [
            'total_reviews' => $reviews->count(),
            'average_rating' => $reviews->avg('rating'),
            'rating_distribution' => [
                '5_star' => $reviews->where('rating', 5)->count(),
                '4_star' => $reviews->where('rating', 4)->count(),
                '3_star' => $reviews->where('rating', 3)->count(),
                '2_star' => $reviews->where('rating', 2)->count(),
                '1_star' => $reviews->where('rating', 1)->count(),
            ]
        ];

        return response()->json(['statistics' => $stats]);
    }

    // Helper method to update service provider's average rating
    private function updateServiceProviderRating($serviceProviderId)
    {
        $averageRating = ServiceReview::where('service_provider_id', $serviceProviderId)
                                     ->avg('rating');

        ServiceProvider::where('id', $serviceProviderId)
                      ->update(['rating' => round($averageRating, 2)]);
    }
}
