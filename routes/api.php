<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Store intended URL in session
Route::post('/set-intended-url', function (Request $request) {
    $intendedUrl = $request->input('intended_url', '/dashboard');
    
    // Validate the URL to ensure it's a local path
    if (filter_var($intendedUrl, FILTER_VALIDATE_URL) === false && str_starts_with($intendedUrl, '/')) {
        session(['url.intended' => $intendedUrl]);
        return response()->json(['success' => true]);
    }
    
    return response()->json(['success' => false, 'message' => 'Invalid URL'], 400);
})->middleware('web');