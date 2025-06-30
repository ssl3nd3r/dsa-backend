<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type',
        'description',
        'contact_info',
        'rating',
        'is_active',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'rating' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
