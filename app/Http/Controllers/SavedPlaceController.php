<?php

namespace App\Http\Controllers;

use App\Models\SavedPlace;
use App\Http\Services\PlaceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavedPlaceController extends Controller
{
    protected $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    /**
     * Display the saved places page
     */
    public function index(Request $request)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Get all places with dynamic ratings
        $allPlaces = $this->placeService->getAllPlacesWithRatings();

        return Inertia::render('SavePlaces', [
            'allPlaces' => $allPlaces
        ]);
    }

    /**
     * Get user's saved place IDs (API endpoint)
     */
    public function getSavedPlaceIds(Request $request)
    {
        $savedPlaceIds = $request->user()
            ->savedPlaces()
            ->pluck('place_id')
            ->toArray();

        return response()->json($savedPlaceIds);
    }

    /**
     * Toggle save/unsave
     */
    public function toggle(Request $request, $placeId)
    {
        $user = $request->user();

        $savedPlace = SavedPlace::where('user_id', $user->id)
            ->where('place_id', $placeId)
            ->first();

        if ($savedPlace) {
            $savedPlace->delete();
            return response()->json([
                'saved' => false,
                'message' => 'Place removed from saved'
            ]);
        } else {
            SavedPlace::create([
                'user_id' => $user->id,
                'place_id' => $placeId
            ]);
            return response()->json([
                'saved' => true,
                'message' => 'Place saved successfully'
            ]);
        }
    }
}