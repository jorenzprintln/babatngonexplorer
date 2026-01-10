<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmPasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): Response
    {
        return Inertia::render('Admin/Settings/AdminConfirmPass');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('The provided password is incorrect.')],
            ]);
        }

        // Set the password confirmation timestamp in the session
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('admin.profile.edit'));
    }
}