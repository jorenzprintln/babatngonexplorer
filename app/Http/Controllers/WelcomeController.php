<?php

namespace App\Http\Controllers;

use App\Http\Services\PlaceService;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    protected $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    /**
     * Display the welcome/landing page
     */
    public function index()
    {
        // Get all places with ratings (limit to 6 for homepage)
        $allPlaces = $this->placeService->getAllPlacesWithRatings();
        $featuredPlaces = array_slice($allPlaces, 0, 6);
        
        // Fetch recent reviews with place information
        $reviews = Review::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(4) // Get latest 4 reviews for homepage
            ->get()
            ->map(function ($review) {
                $place = $this->placeService->getPlaceById($review->place_id);
                
                if (!$place) {
                    return null;
                }
                
                return [
                    'id' => $review->id,
                    'name' => $review->user->name,
                    'avatar' => 'https://i.pravatar.cc/150?u=' . $review->user->id,
                    'rating' => $review->rating,
                    'date' => $review->created_at->format('F Y'),
                    'comment' => $review->comment,
                    'location' => $place['location'] ?? 'Philippines',
                    'place_name' => $place['name'] ?? 'Unknown Place',
                ];
            })
            ->filter()
            ->values();
        
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'places' => $featuredPlaces,
            'reviews' => $reviews,
        ]);
    }
}