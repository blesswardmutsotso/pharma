<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only: ['store', 'show', 'adminUpdate', 'toggleActive']),
        ];
    }

    // ─── Own profile edit (legacy — not used directly) ────────────
    public function edit()
    {
        $user = Auth::user();
        return view('dashboard', compact('user'));
    }

    // ─── Account settings page ────────────────────────────────────
    public function settings()
    {
        $user  = auth()->user();
        $users = User::orderBy('created_at')->get();

        return view('account.account-settings', compact('user', 'users'));
    }

    // ─── Create new user ──────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'new_name'      => 'required|string|max:255',
            'new_email'     => 'required|email|unique:users,email',
            'new_password'  => ['required', 'confirmed', Password::defaults()],
            'new_user_type' => 'required|integer|in:0,1',
            'new_role'      => 'required|string|in:' . implode(',', array_keys(User::roles())),
        ], [
            'new_email.unique'       => 'That email address is already registered.',
            'new_password.confirmed' => 'Passwords do not match.',
        ]);

        User::create([
            'name'      => $request->new_name,
            'email'     => $request->new_email,
            'password'  => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'user_type' => $request->new_user_type,
            'role'      => $request->new_role,
            'is_active' => true,
        ]);
        Cache::forget('users:all');

        return redirect()->route('account.settings')
            ->with('success', "User \"{$request->new_name}\" created successfully.")
            ->with('open_tab', 'users');
    }

    // ─── Return user JSON for edit modal ──────────────────────────
    public function show(User $user)
    {
        return response()->json([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'user_type' => $user->user_type,
            'role'      => $user->role,
            'is_active' => $user->is_active,
            'joined'    => $user->created_at?->format('d M Y'),
        ]);
    }

    // ─── Update own profile ───────────────────────────────────────
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'user_type'    => 'required|integer',
            'password'     => ['nullable', 'confirmed', Password::defaults()],
            'google_id'    => 'nullable|string|unique:users,google_id,' . $user->id,
            'google_token' => 'nullable|string',
        ]);

        $user->name       = $request->name;
        $user->email      = $request->email;
        $user->user_type  = $request->user_type;
        $user->google_id  = $request->google_id;
        $user->google_token = $request->google_token;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_changed_at = now();
        }

        $user->save();

        return redirect()->back()->with('success', 'Account settings updated successfully.');
    }

    // ─── Admin: update any user (from modal) ─────────────────────
    public function adminUpdate(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'user_type' => 'required|integer|in:0,1',
            'role'      => 'required|string|in:' . implode(',', array_keys(User::roles())),
            'password'  => ['nullable', 'confirmed', Password::defaults()],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->user_type = $request->user_type;
        $user->role      = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_changed_at = now();
        }

        $user->save();
        Cache::forget('users:all');

        return response()->json(['success' => true, 'message' => "User \"{$user->name}\" updated successfully."]);
    }

    // ─── Toggle active / inactive ─────────────────────────────────
    public function toggleActive(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json(['success' => false, 'message' => 'You cannot deactivate your own account.'], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();
        Cache::forget('users:all');

        $state = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success'   => true,
            'is_active' => $user->is_active,
            'message'   => "\"{$user->name}\" has been {$state}.",
        ]);
    }

    // ─── Upload profile photo ─────────────────────────────────────
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ]);

        $user = auth()->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return response()->json([
            'success' => true,
            'url'     => Storage::disk('public')->url($path),
            'message' => 'Profile photo updated.',
        ]);
    }

    // ─── Remove profile photo ─────────────────────────────────────
    public function removePhoto()
    {
        $user = auth()->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Profile photo removed.']);
    }

    // ─── Delete own account ───────────────────────────────────────
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        Auth::logout();
        $user->delete();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
