<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use App\Models\User;
use App\Models\Property;
use App\Models\ServiceProvider;

class ReviewController extends Controller
{
    // Create a review for any model
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reviewable_type' => 'required|string|in:App\Models\User,App\Models\Property,App\Models\ServiceProvider',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'review_type' => 'required|string|in:user,property,service_provider',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Verify the reviewable model exists
        $reviewableClass = $request->reviewable_type;
        $reviewable = $reviewableClass::find($request->reviewable_id);
        
        if (!$reviewable) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Prevent self-reviewing
        if ($request->reviewable_type === 'App\Models\User' && $request->reviewable_id == $request->user()->id) {
            return response()->json(['error' => 'Cannot review yourself'], 400);
        }

        // Check if user already reviewed this item
        $existingReview = Review::where('reviewer_id', $request->user()->id)
                               ->where('reviewable_type', $request->reviewable_type)
                               ->where('reviewable_id', $request->reviewable_id)
                               ->first();

        if ($existingReview) {
            return response()->json(['error' => 'You have already reviewed this item'], 400);
        }

        $review = Review::create([
            'reviewer_id' => $request->user()->id,
            'reviewable_type' => $request->reviewable_type,
            'reviewable_id' => $request->reviewable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'review_type' => $request->review_type,
            'is_verified' => false,
        ]);

        $review->load('reviewer:id,name,profile_image');

        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review,
        ], 201);
    }

    // Get reviews for a specific model
    public function index(Request $request, $type, $id)
    {
        $modelMap = [
            'users' => 'App\Models\User',
            'properties' => 'App\Models\Property',
            'service-providers' => 'App\Models\ServiceProvider',
        ];

        if (!array_key_exists($type, $modelMap)) {
            return response()->json(['error' => 'Invalid review type'], 400);
        }

        $modelClass = $modelMap[$type];
        $item = $modelClass::find($id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $reviews = $item->reviews()
                       ->with('reviewer:id,name,profile_image')
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

    // Get user's reviews (reviews written by the user)
    public function myReviews(Request $request)
    {
        $reviews = Review::where('reviewer_id', $request->user()->id)
                        ->with(['reviewable', 'reviewable.owner:id,name' => function($query) {
                            $query->when($query->getModel() instanceof Property, function($q) {
                                $q->select('id', 'name');
                            });
                        }])
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
        $review = Review::where('id', $reviewId)
                       ->where('reviewer_id', $request->user()->id)
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
        $review->load('reviewer:id,name,profile_image');

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review,
        ]);
    }

    // Delete a review
    public function destroy(Request $request, $reviewId)
    {
        $review = Review::where('id', $reviewId)
                       ->where('reviewer_id', $request->user()->id)
                       ->first();

        if (!$review) {
            return response()->json(['error' => 'Review not found or access denied'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    // Get review statistics for a model
    public function statistics($type, $id)
    {
        $modelMap = [
            'users' => 'App\Models\User',
            'properties' => 'App\Models\Property',
            'service-providers' => 'App\Models\ServiceProvider',
        ];

        if (!array_key_exists($type, $modelMap)) {
            return response()->json(['error' => 'Invalid review type'], 400);
        }

        $modelClass = $modelMap[$type];
        $item = $modelClass::find($id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $reviews = $item->reviews();
        
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

    // Get reviews by type
    public function byType(Request $request, $type)
    {
        $typeMap = [
            'users' => 'user',
            'properties' => 'property',
            'service-providers' => 'service_provider',
        ];

        if (!array_key_exists($type, $typeMap)) {
            return response()->json(['error' => 'Invalid review type'], 400);
        }

        $reviews = Review::ofType($typeMap[$type])
                        ->with(['reviewer:id,name,profile_image', 'reviewable'])
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
}
