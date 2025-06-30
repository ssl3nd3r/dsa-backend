<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $areas = [
            'Dubai Marina', 'Downtown Dubai', 'Palm Jumeirah', 'JBR', 'Business Bay',
            'Dubai Hills Estate', 'Arabian Ranches', 'Emirates Hills', 'Meadows',
            'Springs', 'Lakes', 'JLT', 'DIFC', 'Sheikh Zayed Road', 'Al Barsha',
            'Jumeirah', 'Umm Suqeim', 'Al Sufouh', 'Al Quoz', 'Al Khail'
        ];

        $propertyTypes = ['Studio', '1BR', '2BR', '3BR', '4BR+', 'Shared Room', 'Private Room'];
        $roomTypes = ['Entire Place', 'Private Room', 'Shared Room'];
        $currencies = ['AED'];
        $billingCycles = ['Monthly', 'Quarterly', 'Yearly'];
        $statuses = ['Active', 'Pending', 'Rented', 'Inactive'];

        $propertyType = $this->faker->randomElement($propertyTypes);
        $roomType = $this->faker->randomElement($roomTypes);
        
        // Generate appropriate bedrooms and bathrooms based on property type
        $bedrooms = match($propertyType) {
            'Studio' => 0,
            '1BR' => 1,
            '2BR' => 2,
            '3BR' => 3,
            '4BR+' => $this->faker->numberBetween(4, 6),
            'Shared Room', 'Private Room' => 1,
            default => 1
        };

        $bathrooms = $bedrooms > 0 ? $this->faker->numberBetween(1, $bedrooms + 1) : 1;
        $size = $this->faker->numberBetween(400, 3000);

        // Generate realistic prices based on area and property type
        $basePrice = match($propertyType) {
            'Studio' => $this->faker->numberBetween(3000, 8000),
            '1BR' => $this->faker->numberBetween(5000, 12000),
            '2BR' => $this->faker->numberBetween(8000, 18000),
            '3BR' => $this->faker->numberBetween(12000, 25000),
            '4BR+' => $this->faker->numberBetween(18000, 40000),
            'Shared Room' => $this->faker->numberBetween(2000, 5000),
            'Private Room' => $this->faker->numberBetween(3000, 7000),
            default => $this->faker->numberBetween(5000, 15000)
        };

        $amenities = $this->faker->randomElements([
            'WiFi', 'Air Conditioning', 'Gym', 'Pool', 'Parking', 'Balcony',
            'Dishwasher', 'Washing Machine', 'Furnished', 'Security', 'Concierge',
            'Garden', 'BBQ Area', 'Children\'s Playground', 'Tennis Court',
            'Spa', 'Restaurant', 'Supermarket', 'Public Transport'
        ], $this->faker->numberBetween(3, 8));

        $roommatePreferences = $this->faker->randomElements([
            'Non-smoker', 'Pet-friendly', 'Quiet', 'Professional', 'Student-friendly',
            'LGBTQ+ friendly', 'Vegetarian', 'Muslim-friendly', 'International students welcome'
        ], $this->faker->numberBetween(0, 4));

        return [
            'title' => $this->faker->sentence(3, 6),
            'slug' => Str::slug($this->faker->sentence(3, 6)),
            'description' => $this->faker->paragraphs(3, true),
            'area' => $this->faker->randomElement($areas),
            'address' => [
                'street' => $this->faker->streetAddress(),
                'city' => 'Dubai',
                'state' => 'Dubai',
                'country' => 'UAE',
                'postal_code' => $this->faker->postcode()
            ],
            'coordinates' => [
                'lat' => $this->faker->latitude(25.0, 25.5),
                'lng' => $this->faker->longitude(55.0, 55.5)
            ],
            'property_type' => $propertyType,
            'room_type' => $roomType,
            'size' => $size,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'price' => $basePrice,
            'currency' => $this->faker->randomElement($currencies),
            'billing_cycle' => $this->faker->randomElement($billingCycles),
            'utilities_included' => $this->faker->boolean(70),
            'utilities_cost' => $this->faker->boolean(70) ? 0 : $this->faker->numberBetween(200, 800),
            'amenities' => $amenities,
            'available_from' => $this->faker->dateTimeBetween('now', '+3 months'),
            'minimum_stay' => $this->faker->numberBetween(1, 6),
            'maximum_stay' => $this->faker->numberBetween(6, 24),
            'is_available' => $this->faker->boolean(80),
            'images' => [
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-603b3fc33ddc?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-5c9a73c7d9e6?w=800&h=600&fit=crop'
            ],
            'owner_id' => User::factory(),
            'roommate_preferences' => $roommatePreferences,
            'matching_score' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement($statuses),
        ];
    }

    /**
     * Indicate that the property is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => true,
            'status' => 'Active',
        ]);
    }

    /**
     * Indicate that the property is rented.
     */
    public function rented(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
            'status' => 'Rented',
        ]);
    }

    /**
     * Indicate that the property is a studio.
     */
    public function studio(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'Studio',
            'bedrooms' => 0,
            'bathrooms' => 1,
            'size' => $this->faker->numberBetween(400, 800),
        ]);
    }

    /**
     * Indicate that the property is a luxury property.
     */
    public function luxury(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->numberBetween(15000, 50000),
            'area' => $this->faker->randomElement(['Palm Jumeirah', 'Downtown Dubai', 'Dubai Marina']),
            'amenities' => $this->faker->randomElements([
                'WiFi', 'Air Conditioning', 'Gym', 'Pool', 'Parking', 'Balcony',
                'Dishwasher', 'Washing Machine', 'Furnished', 'Security', 'Concierge',
                'Garden', 'BBQ Area', 'Spa', 'Restaurant', 'Supermarket', 'Public Transport'
            ], $this->faker->numberBetween(8, 12)),
        ]);
    }
} 