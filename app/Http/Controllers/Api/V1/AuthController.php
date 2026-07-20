<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        if (!$user->is_active) {
            return $this->error('Your account has been deactivated. Please contact your administrator.', 403);
        }

        // Revoke any existing tokens for this device to prevent accumulation
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name, ['*'])->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'user_type' => $user->user_type,
                'is_admin'  => $user->isAdmin(),
                'is_active' => $user->is_active,
            ],
        ], 'Login successful.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return $this->success([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'user_type' => $user->user_type,
            'is_admin'  => $user->isAdmin(),
            'is_active' => $user->is_active,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        // Revoke all tokens — user must log in again on all devices
        $user->tokens()->delete();

        return $this->success(null, 'Password changed successfully. Please log in again.');
    }
}
