<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\SavedPlaceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;

// Welcome page with dynamic places and reviews
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Dashboard with dynamic stats
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Public route that redirects to login if not authenticated
Route::get('/places', [PlaceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('places.index');

// Protected route - must be authenticated to view place details
Route::get('/places/{id}', [PlaceController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('places.show');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Saved places page
    Route::get('/save', [SavedPlaceController::class, 'index'])->name('saved.places');
    
    // API routes for saved places
    Route::get('/api/saved-places', [SavedPlaceController::class, 'getSavedPlaceIds']);
    Route::post('/api/saved-places/toggle/{placeId}', [SavedPlaceController::class, 'toggle']);
    
    // Review routes
    Route::get('/review', [ReviewController::class, 'myReviews'])->name('review.index');
    Route::post('/places/{id}/reviews', [PlaceController::class, 'storeReview'])->name('places.reviews.store');
    Route::post('/reviews/{reviewId}/report', [PlaceController::class, 'reportReview'])->name('reviews.report');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

require __DIR__.'/settings.php';