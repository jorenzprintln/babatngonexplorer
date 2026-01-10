<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAppearanceController extends Controller
{
    /**
     * Show the form for editing appearance settings.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        
        return Inertia::render('Admin/Settings/Appearance', [
            'theme' => $user->theme ?? 'system',
            'language' => $user->language ?? 'en',
        ]);
    }

    /**
     * Update appearance settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        $request->user()->update($validated);

        return back();
    }
}