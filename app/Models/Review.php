<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'comment',
        'review_type',
        'is_verified',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the parent reviewable model (user, property, or service provider).
     */
    public function reviewable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who wrote the review.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Scope to filter by review type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('review_type', $type);
    }

    /**
     * Scope to filter by reviewable type
     */
    public function scopeForModel($query, $modelType)
    {
        return $query->where('reviewable_type', $modelType);
    }
}
