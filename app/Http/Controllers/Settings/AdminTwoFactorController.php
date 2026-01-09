<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

class AdminTwoFactorController extends Controller
{
    /**
     * Show the two-factor authentication settings.
     */
    public function show(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        
        return Inertia::render('Admin/Settings/TwoFactor/Show', [
            'two_factor_enabled' => !is_null($user->two_factor_secret),
            'two_factor_confirmed' => !is_null($user->two_factor_confirmed_at),
        ]);
    }

    /**
     * Enable two-factor authentication.
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $user->update([
            'two_factor_secret' => encrypt($secret),
        ]);

        return redirect()->route('admin.two-factor.show')
            ->with('qr_code', $qrCodeUrl)
            ->with('secret', $secret)
            ->with('success', 'Two-factor authentication has been enabled.');
    }

    /**
     * Disable two-factor authentication.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('admin.two-factor.show')
            ->with('success', 'Two-factor authentication has been disabled.');
    }
}