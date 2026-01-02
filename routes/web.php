<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PlaceController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/places', [PlaceController::class, 'index'])->name('places.index');
Route::get('/places/{id}', [PlaceController::class, 'show'])->name('places.show');

Route::get('/save', function () {
    return Inertia::render('SavePlaces'); 
})->name('save.index');

Route::get('/review', function () {
    return Inertia::render('ReviewPlaces'); 
})->name('review.index');

require __DIR__.'/settings.php';