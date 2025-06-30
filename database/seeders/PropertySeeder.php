<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users or create some if none exist
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }

        // Create a variety of properties
        $this->createStudioProperties($users);
        $this->createFamilyProperties($users);
        $this->createLuxuryProperties($users);
        $this->createSharedAccommodations($users);
        $this->createBudgetProperties($users);
    }

    /**
     * Create studio properties
     */
    private function createStudioProperties($users): void
    {
        Property::factory(15)
            ->studio()
            ->available()
            ->create([
                'owner_id' => $users->random()->id,
            ]);
    }

    /**
     * Create family properties (2BR, 3BR, 4BR+)
     */
    private function createFamilyProperties($users): void
    {
        // 2BR properties
        Property::factory(20)
            ->available()
            ->create([
                'property_type' => '2BR',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'owner_id' => $users->random()->id,
            ]);

        // 3BR properties
        Property::factory(15)
            ->available()
            ->create([
                'property_type' => '3BR',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'owner_id' => $users->random()->id,
            ]);

        // 4BR+ properties
        Property::factory(10)
            ->available()
            ->create([
                'property_type' => '4BR+',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'owner_id' => $users->random()->id,
            ]);
    }

    /**
     * Create luxury properties
     */
    private function createLuxuryProperties($users): void
    {
        Property::factory(8)
            ->luxury()
            ->available()
            ->create([
                'owner_id' => $users->random()->id,
            ]);
    }

    /**
     * Create shared accommodations
     */
    private function createSharedAccommodations($users): void
    {
        // Shared rooms
        Property::factory(12)
            ->available()
            ->create([
                'property_type' => 'Shared Room',
                'room_type' => 'Shared Room',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'owner_id' => $users->random()->id,
            ]);

        // Private rooms
        Property::factory(10)
            ->available()
            ->create([
                'property_type' => 'Private Room',
                'room_type' => 'Private Room',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'owner_id' => $users->random()->id,
            ]);
    }

    /**
     * Create budget properties
     */
    private function createBudgetProperties($users): void
    {
        Property::factory(10)
            ->available()
            ->create([
                'price' => fake()->numberBetween(2000, 5000),
                'area' => fake()->randomElement(['Al Quoz', 'Al Khail', 'Other']),
                'amenities' => fake()->randomElements([
                    'WiFi', 'Air Conditioning', 'Parking', 'Public Transport'
                ], fake()->numberBetween(2, 4)),
                'owner_id' => $users->random()->id,
            ]);
    }
} 