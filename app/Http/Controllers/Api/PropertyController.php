<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Property;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    // Get all properties with filtering
    public function index(Request $request)
    {
        $query = Property::with('owner')->where('is_available', true);

        // Filter by area
        if ($request->has('area')) {
            $areas = is_array($request->area) ? $request->area : explode(',', $request->area);
            $query->whereIn('area', $areas);
        }

        // Filter by property type
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Filter by room type
        if ($request->has('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by bedrooms
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        // Filter by amenities
        if ($request->has('amenities')) {
            $amenities = explode(',', $request->amenities);
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $properties = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'properties' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    // Get single property
    public function show($slug)
    {
        $property = Property::with('owner')->where('slug', $slug)->first();
        
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }

        return response()->json(['property' => $property->toPublicArray()]);
    }

    // Create new property
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'area' => 'required|string',
            'address' => 'required|array',
            'coordinates' => 'nullable|array',
            'property_type' => 'required|string',
            'room_type' => 'required|string',
            'size' => 'required|integer',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'currency' => 'string|in:AED,USD,EUR',
            'billing_cycle' => 'required|string',
            'utilities_included' => 'boolean',
            'utilities_cost' => 'numeric|min:0',
            'amenities' => 'nullable|array',
            'available_from' => 'required|date',
            'minimum_stay' => 'integer|min:1',
            'maximum_stay' => 'integer|min:1',
            'images' => 'nullable|array',
            'roommate_preferences' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $property = Property::create(array_merge($request->all(), [
            'slug' => Str::slug($request->title),
            'owner_id' => $request->user()->id,
            'currency' => $request->currency ?? 'AED',
            'utilities_included' => $request->utilities_included ?? false,
            'utilities_cost' => $request->utilities_cost ?? 0,
            'minimum_stay' => $request->minimum_stay ?? 1,
            'maximum_stay' => $request->maximum_stay ?? 12,
            'is_available' => true,
            'status' => 'Active',
        ]));

        return response()->json([
            'message' => 'Property created successfully',
            'property' => $property->toPublicArray(),
        ], 201);
    }

    // Update property
    public function update(Request $request, $slug)
    {
        $property = Property::where('slug', $slug)
                           ->where('owner_id', $request->user()->id)
                           ->first();

        if (!$property) {
            return response()->json(['error' => 'Property not found or access denied'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:100',
            'description' => 'string|max:1000',
            'area' => 'string',
            'address' => 'array',
            'coordinates' => 'nullable|array',
            'property_type' => 'string',
            'room_type' => 'string',
            'size' => 'integer',
            'bedrooms' => 'integer|min:0',
            'bathrooms' => 'integer|min:0',
            'price' => 'numeric|min:0',
            'currency' => 'string|in:AED,USD,EUR',
            'billing_cycle' => 'string',
            'utilities_included' => 'boolean',
            'utilities_cost' => 'numeric|min:0',
            'amenities' => 'nullable|array',
            'available_from' => 'date',
            'minimum_stay' => 'integer|min:1',
            'maximum_stay' => 'integer|min:1',
            'is_available' => 'boolean',
            'images' => 'nullable|array',
            'roommate_preferences' => 'nullable|array',
            'status' => 'string|in:Active,Pending,Rented,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $property->update($request->all());

        return response()->json([
            'message' => 'Property updated successfully',
            'property' => $property->toPublicArray(),
        ]);
    }

    // Delete property
    public function destroy(Request $request, $slug)
    {
        $property = Property::where('slug', $slug)
                           ->where('owner_id', $request->user()->id)
                           ->first();

        if (!$property) {
            return response()->json(['error' => 'Property not found or access denied'], 404);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }

    // Get user's properties
    public function myProperties(Request $request)
    {
        $properties = Property::where('owner_id', $request->user()->id)
                             ->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 10));

        return response()->json([
            'properties' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    // Search properties
    public function search(Request $request)
    {
        $query = Property::with('owner')->where('is_available', true);

        // Text search
        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('area', 'like', "%{$searchTerm}%");
            });
        }

        // Apply other filters
        if ($request->has('area')) {
            $areas = is_array($request->area) ? $request->area : explode(',', $request->area);
            $query->whereIn('area', $areas);
        }
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $properties = $query->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 10));

        return response()->json([
            'properties' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }
}
