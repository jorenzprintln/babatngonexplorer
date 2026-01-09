<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminAppearanceController extends Controller
{
    /**
     * Show the form for editing appearance settings.
     */
    public function edit(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        
        return Inertia::render('Admin/Settings/Appearance/Edit', [
            'theme' => $user->theme ?? 'system',
            'language' => $user->language ?? 'en',
        ]);
    }

    /**
     * Update appearance settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update($validated);

        return redirect()->route('admin.appearance.edit')
            ->with('success', 'Appearance settings updated successfully.');
    }
}