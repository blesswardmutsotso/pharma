<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(uniqid()), // Set a random password
                    'google_id' => $googleUser->getId(),
                    'user_type' => 0, // Default user type (adjust as needed)
                ]);
            }

            // Log in the user
            Auth::login($user);

            // Redirect based on user type
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard'); // Admin dashboard
            } else {
                return redirect()->route('dashboard'); // Regular user dashboard
            }

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Google login failed: ' . $e->getMessage());
        }
    }
}
