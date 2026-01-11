<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Place extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'location',
        'category',      // Added for admin categorization
        'image',         // Your existing field
        'image_url',     // For admin uploads (can be same as 'image')
        'rating',        // Your existing field
        'type',          // Your existing field
        'latitude',
        'longitude',
        'status',        // Added for admin to activate/deactivate places
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'decimal:1',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get all reviews for this place
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the saved places for this place.
     */
    public function savedPlaces(): HasMany
    {
        return $this->hasMany(SavedPlace::class);
    }

    /**
     * Get users who saved this place
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_places')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active places.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive places.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the count of reviews
     */
    public function getReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Get average rating (your existing method)
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }
}