<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the user that viewed the place
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a place view
     */
    public static function recordView($userId, $placeId)
    {
        // Update if exists, create if not
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'place_id' => $placeId,
            ],
            [
                'viewed_at' => now(),
            ]
        );
    }
}