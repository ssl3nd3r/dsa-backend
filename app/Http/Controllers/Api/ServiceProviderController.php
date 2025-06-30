<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceProvider;

class ServiceProviderController extends Controller
{
    // Get all service providers
    public function index(Request $request)
    {
        $query = ServiceProvider::where('is_active', true);

        // Filter by service type
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by rating
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Search by name or description
        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $serviceProviders = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'service_providers' => $serviceProviders->items(),
            'pagination' => [
                'current_page' => $serviceProviders->currentPage(),
                'last_page' => $serviceProviders->lastPage(),
                'per_page' => $serviceProviders->perPage(),
                'total' => $serviceProviders->total(),
            ]
        ]);
    }

    // Get single service provider
    public function show($id)
    {
        $serviceProvider = ServiceProvider::with('reviews.user')->find($id);
        
        if (!$serviceProvider) {
            return response()->json(['error' => 'Service provider not found'], 404);
        }

        return response()->json(['service_provider' => $serviceProvider]);
    }

    // Create new service provider
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'service_type' => 'required|string|max:255',
            'description' => 'required|string',
            'contact_info' => 'required|array',
            'rating' => 'numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $serviceProvider = ServiceProvider::create([
            'name' => $request->name,
            'service_type' => $request->service_type,
            'description' => $request->description,
            'contact_info' => $request->contact_info,
            'rating' => $request->rating ?? 0,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Service provider created successfully',
            'service_provider' => $serviceProvider,
        ], 201);
    }

    // Update service provider
    public function update(Request $request, $id)
    {
        $serviceProvider = ServiceProvider::find($id);

        if (!$serviceProvider) {
            return response()->json(['error' => 'Service provider not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'service_type' => 'string|max:255',
            'description' => 'string',
            'contact_info' => 'array',
            'rating' => 'numeric|min:0|max:5',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $serviceProvider->update($request->all());

        return response()->json([
            'message' => 'Service provider updated successfully',
            'service_provider' => $serviceProvider,
        ]);
    }

    // Delete service provider
    public function destroy($id)
    {
        $serviceProvider = ServiceProvider::find($id);

        if (!$serviceProvider) {
            return response()->json(['error' => 'Service provider not found'], 404);
        }

        $serviceProvider->delete();

        return response()->json(['message' => 'Service provider deleted successfully']);
    }

    // Search service providers
    public function search(Request $request)
    {
        $query = ServiceProvider::where('is_active', true);

        // Text search
        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('service_type', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by service type
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by rating
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        $serviceProviders = $query->orderBy('rating', 'desc')
                                 ->paginate($request->get('per_page', 10));

        return response()->json([
            'service_providers' => $serviceProviders->items(),
            'pagination' => [
                'current_page' => $serviceProviders->currentPage(),
                'last_page' => $serviceProviders->lastPage(),
                'per_page' => $serviceProviders->perPage(),
                'total' => $serviceProviders->total(),
            ]
        ]);
    }

    // Get service providers by type
    public function byType(Request $request, $serviceType)
    {
        $serviceProviders = ServiceProvider::where('service_type', $serviceType)
                                         ->where('is_active', true)
                                         ->orderBy('rating', 'desc')
                                         ->paginate($request->get('per_page', 10));

        return response()->json([
            'service_providers' => $serviceProviders->items(),
            'pagination' => [
                'current_page' => $serviceProviders->currentPage(),
                'last_page' => $serviceProviders->lastPage(),
                'per_page' => $serviceProviders->perPage(),
                'total' => $serviceProviders->total(),
            ]
        ]);
    }

    // Get service types
    public function serviceTypes()
    {
        $types = ServiceProvider::where('is_active', true)
                               ->distinct()
                               ->pluck('service_type');

        return response()->json(['service_types' => $types]);
    }
}
