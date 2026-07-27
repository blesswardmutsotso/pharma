<?php

namespace Tests\Feature\Pharma;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_is_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'logtest@example.com',
            'password' => bcrypt('Secure123!'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => 'logtest@example.com',
            'password' => 'Secure123!',
        ]);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => UserActivityLog::LOGIN,
        ]);
    }

    public function test_failed_login_with_wrong_password_is_logged_against_the_matched_user(): void
    {
        $user = User::factory()->create([
            'email' => 'logtest2@example.com',
            'password' => bcrypt('Secure123!'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => 'logtest2@example.com',
            'password' => 'WrongPassword!',
        ]);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => UserActivityLog::FAILED_LOGIN,
        ]);
    }

    public function test_failed_login_with_unknown_email_is_logged_with_no_user_id(): void
    {
        $this->post('/login', [
            'email' => 'nobody-like-this@example.com',
            'password' => 'WhateverPassword!',
        ]);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => null,
            'user_name' => 'nobody-like-this@example.com',
            'action' => UserActivityLog::FAILED_LOGIN,
        ]);
    }

    public function test_logout_is_logged(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($user);

        $this->post('/logout');

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => UserActivityLog::LOGOUT,
        ]);
    }

    public function test_only_admin_can_view_user_activity_logs(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'is_active' => true]);
        $this->actingAs($sales);
        $this->get('/user-activity-logs')->assertForbidden();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($admin);
        $this->get('/user-activity-logs')->assertOk();
    }
}
