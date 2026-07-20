<?php

namespace Tests\Feature\Pharma;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_expired_password_is_redirected_to_change_it(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => User::ROLE_ADMIN,
            'password_changed_at' => now()->subDays(91),
        ]);
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('profile.edit'));
    }

    public function test_user_with_fresh_password_is_not_redirected(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => User::ROLE_ADMIN,
            'password_changed_at' => now()->subDays(10),
        ]);
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
    }

    public function test_weak_password_is_rejected_on_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Weak Password User',
            'email' => 'weak@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);
    }
}
