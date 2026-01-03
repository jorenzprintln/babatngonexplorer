<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Http\Services\PlaceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    protected $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    public function myReviews(Request $request)
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->withCount('reports')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                // Get place data from PlaceService
                $place = $this->placeService->getPlaceById($review->place_id);
                
                if (!$place) {
                    return null; // Skip if place not found
                }
                
                return [
                    'id' => $review->id,
                    'place_id' => $review->place_id,
                    'place_name' => $place['name'],
                    'place_image' => $place['images'][0] ?? '',
                    'place_type' => $place['type'],
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'photos' => $review->photos ? array_map(function($photo) {
                        return Storage::url($photo);
                    }, $review->photos) : [],
                    'created_at' => $review->created_at->toISOString(),
                    'updated_at' => $review->updated_at->toISOString(),
                    'reports_count' => $review->reports_count,
                ];
            })
            ->filter() // Remove null values
            ->values(); // Reset array keys

        return Inertia::render('ReviewPlaces', [
            'reviews' => $reviews
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Delete photos from storage
        if ($review->photos) {
            foreach ($review->photos as $photo) {
                Storage::delete($photo);
            }
        }

        $review->delete();

        return redirect()->route('review.index')
            ->with('success', 'Review deleted successfully');
    }
}