<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'area',
        'address',
        'coordinates',
        'property_type',
        'room_type',
        'size',
        'bedrooms',
        'bathrooms',
        'price',
        'currency',
        'billing_cycle',
        'utilities_included',
        'utilities_cost',
        'amenities',
        'available_from',
        'minimum_stay',
        'maximum_stay',
        'is_available',
        'images',
        'owner_id',
        'roommate_preferences',
        'matching_score',
        'status',
    ];

    protected $casts = [
        'address' => 'array',
        'coordinates' => 'array',
        'amenities' => 'array',
        'images' => 'array',
        'roommate_preferences' => 'array',
        'utilities_included' => 'boolean',
        'is_available' => 'boolean',
        'available_from' => 'date',
        'matching_score' => 'integer',
    ];

    /**
     * Get public data (without sensitive info)
     */
    public function toPublicArray()
    {
        $propertyArray = $this->toArray();
        unset($propertyArray['owner_id']);
        return $propertyArray;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return "{$this->price} {$this->currency}";
    }

    /**
     * Relationships
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
