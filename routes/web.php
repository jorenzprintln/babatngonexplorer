<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\SavedPlaceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Admin\AdminDashboardController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

// Admin Settings Controllers
use App\Http\Controllers\Settings\AdminProfileController;
use App\Http\Controllers\Settings\AdminPasswordController;
use App\Http\Controllers\Settings\AdminTwoFactorController;
use App\Http\Controllers\Settings\AdminAppearanceController;

// Logout route - works for both regular users and admins
Route::post('/logout', function (Illuminate\Http\Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Password Confirmation Routes (must be BEFORE settings routes)
    Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])
        ->middleware(['web'])
        ->name('password.confirm');
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])
        ->middleware(['web']);
    
    // Settings redirect - redirects to profile as default
    Route::get('/settings', function () {
        return redirect()->route('admin.profile.edit');
    })->name('settings');
    
    // Settings Routes - ALL require password confirmation
    Route::prefix('settings')->middleware(['password.confirm'])->group(function () {
        // Profile
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        
        // Password
        Route::get('/password', [AdminPasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [AdminPasswordController::class, 'update'])->name('password.update');
        
        // Two-Factor Authentication
        Route::get('/two-factor', [AdminTwoFactorController::class, 'show'])->name('two-factor.show');
        Route::post('/two-factor', [AdminTwoFactorController::class, 'store'])->name('two-factor.store');
        Route::delete('/two-factor', [AdminTwoFactorController::class, 'destroy'])->name('two-factor.destroy');
        
        // Two-Factor QR Code
        Route::get('/two-factor-qr-code', function (Illuminate\Http\Request $request) {
            return response()->json([
                'svg' => $request->user()->twoFactorQrCodeSvg(),
            ]);
        })->name('two-factor.qr-code');
        
        // Two-Factor Recovery Codes
        Route::get('/two-factor-recovery-codes', function (Illuminate\Http\Request $request) {
            return response()->json([
                'codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true),
            ]);
        })->name('two-factor.recovery-codes');
        
        // Two-Factor Confirmation
        Route::post('/confirmed-two-factor-authentication', function (
            Illuminate\Http\Request $request,
            Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication $confirm
        ) {
            $confirm($request->user(), $request->input('code'));
            return back();
        })->name('two-factor.confirm');
        
        // Appearance
        Route::get('/appearance', [AdminAppearanceController::class, 'edit'])->name('appearance.edit');
        Route::patch('/appearance', [AdminAppearanceController::class, 'update'])->name('appearance.update');
    });
});
// User settings routes - ONLY ONCE at the end
require __DIR__.'/settings.php';