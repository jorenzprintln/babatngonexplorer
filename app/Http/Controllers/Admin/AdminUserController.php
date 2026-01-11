<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Review;
use App\Models\SavedPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->onlyTrashed();
            }
        }

        // Order by newest first
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $users = $query->paginate(15)->withQueryString();

        // Transform the data
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
                'reviews_count' => Review::where('user_id', $user->id)->count(),
                'saved_places_count' => SavedPlace::where('user_id', $user->id)->count(),
            ];
        });

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'role' => $request->role,
                'status' => $request->status,
            ],
            'stats' => [
                'total_users' => User::count(),
                'admin_users' => User::where('role', 'admin')->count(),
                'regular_users' => User::where('role', 'user')->orWhereNull('role')->count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
            ],
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $reviews = Review::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $savedPlaces = SavedPlace::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
                'reviews_count' => Review::where('user_id', $user->id)->count(),
                'saved_places_count' => SavedPlace::where('user_id', $user->id)->count(),
            ],
            'recent_reviews' => $reviews,
            'recent_saved_places' => $savedPlaces,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:user,admin',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(), // Auto-verify admin created users
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'email_verified_at' => $user->email_verified_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user role between admin and user.
     */
    public function toggleRole(User $user)
    {
        // Prevent changing your own role
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role!');
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';
        $user->update(['role' => $newRole]);

        return back()->with('success', 'User role updated successfully!');
    }

    /**
     * Verify user email.
     */
    public function verifyEmail(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('error', 'Email already verified!');
        }

        $user->update(['email_verified_at' => now()]);

        return back()->with('success', 'Email verified successfully!');
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Remove current user from the list
        $userIds = array_filter($validated['user_ids'], function($id) {
            return $id !== auth()->id();
        });

        User::whereIn('id', $userIds)->delete();

        return back()->with('success', count($userIds) . ' users deleted successfully!');
    }

    /**
     * Bulk update user roles.
     */
    public function bulkUpdateRole(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:user,admin',
        ]);

        // Remove current user from the list
        $userIds = array_filter($validated['user_ids'], function($id) {
            return $id !== auth()->id();
        });

        User::whereIn('id', $userIds)
            ->update(['role' => $validated['role']]);

        return back()->with('success', count($userIds) . ' users updated successfully!');
    }
}