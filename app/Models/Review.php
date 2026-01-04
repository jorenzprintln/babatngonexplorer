<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'user_id',
        'rating',
        'comment',
        'photos',
    ];

    protected $casts = [
        'rating' => 'integer',
        'photos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reports for this review
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    /**
     * Get the place being reviewed
     * This returns a virtual relationship for MongoDB place_id
     */
    public function getPlaceAttribute()
    {
        // Cache the place data to avoid multiple API calls
        if (!isset($this->attributes['_place_cache'])) {
            $placeService = app(\App\Http\Services\PlaceService::class);
            $placeData = $placeService->getPlaceWithRatings($this->place_id);
            
            if (!$placeData) {
                $this->attributes['_place_cache'] = (object) [
                    'id' => $this->place_id,
                    'name' => 'Unknown Place',
                    'image' => '',
                    'type' => 'Place',
                ];
            } else {
                // Handle both 'image' and 'images' array
                $image = '';
                if (isset($placeData['image'])) {
                    $image = $placeData['image'];
                } elseif (isset($placeData['images']) && is_array($placeData['images']) && count($placeData['images']) > 0) {
                    $image = $placeData['images'][0];
                }
                
                $this->attributes['_place_cache'] = (object) [
                    'id' => $placeData['id'],
                    'name' => $placeData['name'] ?? 'Unknown Place',
                    'image' => $image,
                    'type' => $placeData['type'] ?? 'Place',
                    'location' => $placeData['location'] ?? '',
                ];
            }
        }
        
        return $this->attributes['_place_cache'];
    }

    /**
     * Get reviews for a specific place
     */
    public static function getForPlace($placeId)
    {
        return static::where('place_id', $placeId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get average rating for a place
     */
    public static function getAverageRating($placeId)
    {
        return static::where('place_id', $placeId)->avg('rating') ?? 0;
    }

    /**
     * Get review count for a place
     */
    public static function getReviewCount($placeId)
    {
        return static::where('place_id', $placeId)->count();
    }
}