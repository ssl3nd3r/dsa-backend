# Database Seeders

This directory contains database seeders for populating the application with test data.

## PropertySeeder

The `PropertySeeder` creates realistic property data for the Dubai accommodations platform.

### What it creates:

- **90+ properties** across different categories:
  - 15 Studio apartments
  - 20 2BR apartments
  - 15 3BR apartments
  - 10 4BR+ apartments
  - 8 Luxury properties
  - 12 Shared rooms
  - 10 Private rooms
  - 10 Budget properties

### Property Data Includes:

- **Location**: Realistic Dubai areas (Dubai Marina, Downtown Dubai, Palm Jumeirah, etc.)
- **Pricing**: Market-appropriate prices based on property type and location
- **Amenities**: Common amenities like WiFi, AC, Gym, Pool, Parking, etc.
- **Roommate Preferences**: For shared accommodations
- **Images**: Sample property images from Unsplash
- **Availability**: Mix of available and rented properties

### How to run:

#### Run all seeders (including properties):
```bash
php artisan db:seed
```

#### Run only the PropertySeeder:
```bash
php artisan db:seed --class=PropertySeeder
```

#### Run with custom count:
```bash
php artisan db:seed --class=PropertySeeder
```

### Factory States:

The `PropertyFactory` includes several states for creating specific types of properties:

- `available()` - Sets property as available
- `rented()` - Sets property as rented
- `studio()` - Creates studio apartments
- `luxury()` - Creates luxury properties with premium amenities

### Example Usage:

```php
// Create 10 available studio properties
Property::factory(10)->studio()->available()->create();

// Create 5 luxury properties
Property::factory(5)->luxury()->create();

// Create a single property with custom data
Property::factory()->create([
    'title' => 'Custom Property',
    'price' => 15000,
    'area' => 'Dubai Marina'
]);
```

### Data Structure:

Each property includes:
- Basic info (title, description, area)
- Location (address, coordinates)
- Property details (type, size, bedrooms, bathrooms)
- Pricing (price, currency, billing cycle, utilities)
- Amenities (array of available amenities)
- Availability (dates, minimum/maximum stay)
- Images (array of image URLs)
- Owner relationship
- Roommate preferences (for shared accommodations)
- Status and availability flags

The seeder automatically creates users if none exist, ensuring all properties have valid owners. 