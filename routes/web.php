<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/places', function () {
        return Inertia::render('ExplorePlaces'); 
})->name('places.index');

Route::get('/save', function () {
        return Inertia::render('SavePlaces'); 
})->name('save.index');

Route::get('/review', function () {
        return Inertia::render('ReviewPlaces'); 
})->name('review.index');
require __DIR__.'/settings.php';
