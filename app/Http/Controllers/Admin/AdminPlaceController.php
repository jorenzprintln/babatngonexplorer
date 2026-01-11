<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminPlaceController extends Controller
{
    /**
     * Display a listing of places.
     */
    public function index(Request $request)
    {
        $query = Place::query()->withCount('reviews')->withAvg('reviews', 'rating');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order by newest first
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $places = $query->paginate(10)->withQueryString();

        // Transform the data
        $places->getCollection()->transform(function ($place) {
            // Use 'image' or 'image_url' field, whichever exists
            $imageUrl = null;
            if ($place->image_url) {
                $imageUrl = Storage::url($place->image_url);
            } elseif ($place->image) {
                $imageUrl = Storage::url($place->image);
            }
            
            return [
                'id' => $place->id,
                'name' => $place->name,
                'description' => $place->description,
                'location' => $place->location,
                'category' => $place->category ?? $place->type, // Fallback to 'type' if 'category' doesn't exist
                'image_url' => $imageUrl,
                'status' => $place->status ?? 'active',
                'average_rating' => round($place->reviews_avg_rating ?? 0, 1),
                'reviews_count' => $place->reviews_count ?? 0,
                'created_at' => $place->created_at->toISOString(),
                'updated_at' => $place->updated_at->toISOString(),
            ];
        });

        return Inertia::render('Admin/Places', [
            'places' => $places,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new place.
     */
    public function create()
    {
        return Inertia::render('Admin/Places/Create', [
            'categories' => $this->getCategories(),
        ]);
    }

    /**
     * Store a newly created place in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload - store in 'image' field to match existing structure
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('places', 'public');
            // Also set image_url for consistency if the column exists
            $validated['image_url'] = $validated['image'];
        }

        $place = Place::create($validated);

        return redirect()->route('admin.places.index')
            ->with('success', 'Place created successfully!');
    }

    /**
     * Display the specified place.
     */
    public function show(Place $place)
    {
        $place->load(['reviews' => function ($query) {
            $query->with('user')->latest()->take(10);
        }]);

        // Use 'image' or 'image_url' field, whichever exists
        $imageUrl = null;
        if ($place->image_url) {
            $imageUrl = Storage::url($place->image_url);
        } elseif ($place->image) {
            $imageUrl = Storage::url($place->image);
        }

        return Inertia::render('Admin/Places/Show', [
            'place' => [
                'id' => $place->id,
                'name' => $place->name,
                'description' => $place->description,
                'location' => $place->location,
                'category' => $place->category ?? $place->type,
                'latitude' => $place->latitude,
                'longitude' => $place->longitude,
                'image_url' => $imageUrl,
                'status' => $place->status ?? 'active',
                'average_rating' => round($place->reviews()->avg('rating') ?? 0, 1),
                'reviews_count' => $place->reviews()->count(),
                'created_at' => $place->created_at->toISOString(),
                'updated_at' => $place->updated_at->toISOString(),
                'reviews' => $place->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->toISOString(),
                        'user' => [
                            'id' => $review->user->id,
                            'name' => $review->user->name,
                            'email' => $review->user->email,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified place.
     */
    public function edit(Place $place)
    {
        // Use 'image' or 'image_url' field, whichever exists
        $imageUrl = null;
        if ($place->image_url) {
            $imageUrl = Storage::url($place->image_url);
        } elseif ($place->image) {
            $imageUrl = Storage::url($place->image);
        }

        return Inertia::render('Admin/Places/Edit', [
            'place' => [
                'id' => $place->id,
                'name' => $place->name,
                'description' => $place->description,
                'location' => $place->location,
                'category' => $place->category ?? $place->type,
                'latitude' => $place->latitude,
                'longitude' => $place->longitude,
                'image_url' => $imageUrl,
                'status' => $place->status ?? 'active',
            ],
            'categories' => $this->getCategories(),
        ]);
    }

    /**
     * Update the specified place in storage.
     */
    public function update(Request $request, Place $place)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists (check both fields)
            if ($place->image_url) {
                Storage::disk('public')->delete($place->image_url);
            } elseif ($place->image) {
                Storage::disk('public')->delete($place->image);
            }
            
            // Store new image in both fields for consistency
            $validated['image'] = $request->file('image')->store('places', 'public');
            $validated['image_url'] = $validated['image'];
        }

        $place->update($validated);

        return redirect()->route('admin.places.index')
            ->with('success', 'Place updated successfully!');
    }

    /**
     * Remove the specified place from storage.
     */
    public function destroy(Place $place)
    {
        // Delete image if exists (check both fields)
        if ($place->image_url) {
            Storage::disk('public')->delete($place->image_url);
        } elseif ($place->image) {
            Storage::disk('public')->delete($place->image);
        }

        // Delete the place (reviews will be cascade deleted if foreign key is set)
        $place->delete();

        return redirect()->route('admin.places.index')
            ->with('success', 'Place deleted successfully!');
    }

    /**
     * Get available categories.
     */
    private function getCategories()
    {
        return [
            'Restaurant',
            'Cafe',
            'Park',
            'Museum',
            'Beach',
            'Shopping',
            'Hotel',
            'Entertainment',
            'Other',
        ];
    }

    /**
     * Toggle place status (active/inactive).
     */
    public function toggleStatus(Place $place)
    {
        $place->update([
            'status' => $place->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Place status updated successfully!');
    }
}