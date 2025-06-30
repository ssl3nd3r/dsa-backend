<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('title', 100);
            $table->string('slug', 150)->unique();
            $table->text('description');
            
            // Location
            $table->enum('area', [
                'Dubai Marina', 'Downtown Dubai', 'Palm Jumeirah', 'JBR', 'Business Bay',
                'Dubai Hills Estate', 'Arabian Ranches', 'Emirates Hills', 'Meadows',
                'Springs', 'Lakes', 'JLT', 'DIFC', 'Sheikh Zayed Road', 'Al Barsha',
                'Jumeirah', 'Umm Suqeim', 'Al Sufouh', 'Al Quoz', 'Al Khail', 'Other'
            ]);
            $table->json('address');
            $table->json('coordinates')->nullable();
            
            // Property Details
            $table->enum('property_type', ['Studio', '1BR', '2BR', '3BR', '4BR+', 'Shared Room', 'Private Room']);
            $table->enum('room_type', ['Entire Place', 'Private Room', 'Shared Room']);
            $table->integer('size'); // in sq ft
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->enum('currency', ['AED', 'USD', 'EUR'])->default('AED');
            $table->enum('billing_cycle', ['Monthly', 'Quarterly', 'Yearly']);
            $table->boolean('utilities_included')->default(false);
            $table->decimal('utilities_cost', 8, 2)->default(0);
            
            // Amenities
            $table->json('amenities')->nullable();
            
            // Availability
            $table->date('available_from');
            $table->integer('minimum_stay')->default(1); // in months
            $table->integer('maximum_stay')->default(12); // in months
            $table->boolean('is_available')->default(true);
            
            // Images
            $table->json('images')->nullable();
            
            // Owner Information
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            // Roommate Preferences (for shared accommodations)
            $table->json('roommate_preferences')->nullable();
            
            // AI Matching Score (computed)
            $table->integer('matching_score')->default(0);
            
            // Status
            $table->enum('status', ['Active', 'Pending', 'Rented', 'Inactive'])->default('Active');
            
            $table->timestamps();
            
            // Indexes for search optimization
            $table->index(['area', 'price', 'property_type', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
