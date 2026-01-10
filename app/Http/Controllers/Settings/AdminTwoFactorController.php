<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class AdminTwoFactorController extends Controller
{
    /**
     * Show the two-factor authentication settings.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        
        return Inertia::render('Admin/Settings/TwoFactor', [
            'requiresConfirmation' => config('fortify.features.confirmPassword', false),
            'twoFactorEnabled' => ! is_null($user->two_factor_secret),
        ]);
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($request->user());

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable($request->user());

        return back()->with('status', 'two-factor-authentication-disabled');
    }
}