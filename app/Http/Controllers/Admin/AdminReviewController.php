<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Services\PlaceService;

class AdminReviewController extends Controller
{
    protected $placeService;

    public function __construct(PlaceService $placeService)
    {
        $this->placeService = $placeService;
    }

    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $query = Review::query()->with(['user']); // Remove 'place' from eager loading

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Place filter
        if ($request->filled('place_id')) {
            $query->where('place_id', $request->place_id);
        }

        // Order by newest first
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $reviews = $query->paginate(15)->withQueryString();

        // Transform the data - the 'place' accessor will be called automatically
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status ?? 'approved',
                'created_at' => $review->created_at->toISOString(),
                'updated_at' => $review->updated_at->toISOString(),
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'email' => $review->user->email,
                ],
                'place' => [
                    'id' => $review->place->id,
                    'name' => $review->place->name,
                    'location' => $review->place->location ?? '',
                ],
            ];
        });

        // Get all places for filter dropdown from PlaceService
        $allPlaces = $this->placeService->getAllPlacesWithRatings();
        $places = collect($allPlaces)->map(function($place) {
            return [
                'id' => $place['id'],
                'name' => $place['name']
            ];
        })->sortBy('name')->values();

        return Inertia::render('Admin/Reviews', [
            'reviews' => $reviews,
            'places' => $places,
            'filters' => [
                'search' => $request->search,
                'rating' => $request->rating,
                'status' => $request->status,
                'place_id' => $request->place_id,
            ],
            'stats' => [
                'total_reviews' => Review::count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
                'approved_reviews' => Review::where('status', 'approved')->count(),
                'average_rating' => round(Review::avg('rating') ?? 0, 1),
            ],
        ]);
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load(['user']); // Remove 'place' from eager loading

        return Inertia::render('Admin/Reviews/Show', [
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status ?? 'approved',
                'created_at' => $review->created_at->toISOString(),
                'updated_at' => $review->updated_at->toISOString(),
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'email' => $review->user->email,
                ],
                'place' => [
                    'id' => $review->place->id,
                    'name' => $review->place->name,
                    'location' => $review->place->location ?? '',
                    'type' => $review->place->type ?? 'Place',
                ],
            ],
        ]);
    }

    /**
     * Update the specified review status.
     */
    public function updateStatus(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review->update($validated);

        return back()->with('success', 'Review status updated successfully!');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully!');
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return back()->with('success', 'Review approved successfully!');
    }

    /**
     * Reject a review.
     */
    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return back()->with('success', 'Review rejected successfully!');
    }

    /**
     * Bulk delete reviews.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
        ]);

        Review::whereIn('id', $validated['review_ids'])->delete();

        return back()->with('success', count($validated['review_ids']) . ' reviews deleted successfully!');
    }

    /**
     * Bulk update review status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        Review::whereIn('id', $validated['review_ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', count($validated['review_ids']) . ' reviews updated successfully!');
    }
}