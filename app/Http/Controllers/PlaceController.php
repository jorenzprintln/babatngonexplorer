<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReport;
use App\Http\Services\PlaceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class PlaceController extends Controller
{
    protected $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    /**
     * Display listing of all places
     */
    public function index()
    {
        $places = $this->placeService->getAllPlacesWithRatings();
        
        return Inertia::render('ExplorePlaces', [
            'places' => $places
        ]);
    }

    /**
     * Display a specific place
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $place = $this->placeService->getPlaceWithRatings($id);

        if (!$place) {
            abort(404, 'Place not found');
        }

        $reviews = $this->placeService->getFormattedReviews($id, $request->user()->id);

        return Inertia::render('PlaceDetails', [
            'place' => $place,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Store a new review
     */
    public function storeReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
            'photos.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user already reviewed this place
        $existingReview = Review::where('place_id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return back()->withErrors([
                'review' => 'You have already reviewed this place. You can only submit one review per place.'
            ]);
        }

        // Handle photo uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('review-photos', 'public');
                $photoPaths[] = $path;
            }
        }

        // Create the review
        Review::create([
            'place_id' => $id,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'photos' => $photoPaths,
        ]);

        return back()->with('success', 'Thank you for your review! Your feedback helps others discover this amazing place.');
    }

    /**
     * Report a review
     */
    public function reportReview(Request $request, $reviewId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|in:spam,inappropriate,offensive,misleading,other',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Check if review exists
        Review::findOrFail($reviewId);

        // Check if user already reported this review
        $existingReport = ReviewReport::where('review_id', $reviewId)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReport) {
            return back()->withErrors([
                'report' => 'You have already reported this review.'
            ]);
        }

        // Create the report
        ReviewReport::create([
            'review_id' => $reviewId,
            'user_id' => $request->user()->id,
            'reason' => $request->reason,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Report submitted successfully. We will review it shortly.');
    }
}