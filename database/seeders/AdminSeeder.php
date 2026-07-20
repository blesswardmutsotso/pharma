<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'blesswardmutsotso404@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('leaflight2026'),
                'password_changed_at' => now(),
                'user_type' => 0,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'google_id' => null,
                'google_token' => null,
            ]
        );
    }
}
