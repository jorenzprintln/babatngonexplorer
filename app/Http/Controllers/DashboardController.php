<?php

namespace App\Http\Controllers;

use App\Http\Services\PlaceService;
use App\Models\SavedPlace;
use App\Models\Review;
use App\Models\PlaceView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    /**
     * Display the user dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get stats
        $savedPlacesCount = SavedPlace::where('user_id', $user->id)->count();
        $reviewsCount = Review::where('user_id', $user->id)->count();
        
        // Get recently viewed places (last 4)
        $recentlyViewedIds = PlaceView::where('user_id', $user->id)
            ->orderBy('viewed_at', 'desc')
            ->limit(4)
            ->pluck('place_id')
            ->toArray();
        
        // Get count of unique places that user has reviewed
        $reviewedPlacesCount = Review::where('user_id', $user->id)
            ->distinct('place_id')
            ->count('place_id');
        
        // Get place details for recently viewed
        $recentPlaces = [];
        foreach ($recentlyViewedIds as $placeId) {
            $place = $this->placeService->getPlaceWithRatings($placeId);
            if ($place) {
                // Handle both 'image' and 'images' array
                $image = '';
                if (isset($place['image'])) {
                    $image = $place['image'];
                } elseif (isset($place['images']) && is_array($place['images']) && count($place['images']) > 0) {
                    $image = $place['images'][0];
                }
                
                $recentPlaces[] = [
                    'id' => $place['id'],
                    'name' => $place['name'],
                    'location' => $place['location'],
                    'image' => $image,
                    'type' => $place['type'],
                    'rating' => $place['rating'],
                ];
            }
        }
        
        // Get recent activity (last 10 actions)
        $recentActivity = [];
        
        // Get recent reviews
        $recentReviews = Review::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'action' => 'Reviewed',
                    'place' => $review->place->name ?? 'Unknown Place',
                    'time' => $this->getTimeAgo($review->created_at),
                    'timestamp' => $review->created_at,
                ];
            });
        
        // Get recent saved places
        $recentSaved = SavedPlace::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($saved) {
                $place = $this->placeService->getPlaceWithRatings($saved->place_id);
                return [
                    'action' => 'Saved',
                    'place' => $place['name'] ?? 'Unknown Place',
                    'time' => $this->getTimeAgo($saved->created_at),
                    'timestamp' => $saved->created_at,
                ];
            });
        
        // Get recent views
        $recentViews = PlaceView::where('user_id', $user->id)
            ->orderBy('viewed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($view) {
                $place = $this->placeService->getPlaceWithRatings($view->place_id);
                return [
                    'action' => 'Viewed',
                    'place' => $place['name'] ?? 'Unknown Place',
                    'time' => $this->getTimeAgo($view->viewed_at),
                    'timestamp' => $view->viewed_at,
                ];
            });
        
        // Merge and sort all activities by timestamp
        $allActivities = $recentReviews->concat($recentSaved)->concat($recentViews);
        $recentActivity = $allActivities->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->map(function ($activity) {
                unset($activity['timestamp']); // Remove timestamp from final output
                return $activity;
            })
            ->toArray();
        
        // Get member since date
        $memberSince = $user->created_at->format('M Y');
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'savedPlaces' => $savedPlacesCount,
                'reviewsSubmitted' => $reviewsCount,
                'recentlyViewed' => count($recentPlaces),
            ],
            'recentPlaces' => $recentPlaces,
            'memberSince' => $memberSince,
            'reviewedPlacesCount' => $reviewedPlacesCount,
            'recentActivity' => $recentActivity,
        ]);
    }
    
    /**
     * Convert timestamp to human-readable time ago format
     */
    private function getTimeAgo($datetime)
    {
        $now = now();
        $diff = $datetime->diffInSeconds($now);
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' ' . ($minutes == 1 ? 'minute' : 'minutes') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' ' . ($months == 1 ? 'month' : 'months') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' ' . ($years == 1 ? 'year' : 'years') . ' ago';
        }
    }
}