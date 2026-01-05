<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\Review;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_places' => Place::count(),
            'total_reviews' => Review::count(),
            'total_users' => User::where('role', 'user')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
        ];

        $recentPlaces = Place::latest()->take(5)->get();
        
        // Remove 'place' from with() since it's an accessor, not a relationship
        // The 'place' data will be automatically included because of $appends in the Review model
        $recentReviews = Review::with(['user'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentPlaces' => $recentPlaces,
            'recentReviews' => $recentReviews,
        ]);
    }
}