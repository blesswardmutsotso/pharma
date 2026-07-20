<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAuthenticatedUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 3,
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_client_index_and_create_pages_are_available(): void
    {
        $this->actingAsAuthenticatedUser();

        $this->get('/clients')->assertOk();
        $this->get('/clients/create')->assertOk();
    }

    public function test_client_can_be_created_with_full_details(): void
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->post('/clients', [
            'name' => 'Harare Central Pharmacy',
            'contact_person' => 'Jane Moyo',
            'phone' => '+263771234567',
            'email' => 'jane@hcp.co.zw',
            'vat_number' => '220308672',
            'tin' => '2001001000',
            'province' => 'Harare',
            'city' => 'Harare',
            'district' => 'Harare Central',
            'street' => 'Samora Machel Ave',
            'house_no' => '12',
        ]);

        $client = Client::where('name', 'Harare Central Pharmacy')->firstOrFail();
        $response->assertRedirect(route('clients.show', $client));

        $this->assertDatabaseHas('clients', [
            'name' => 'Harare Central Pharmacy',
            'contact_person' => 'Jane Moyo',
            'vat_number' => '220308672',
            'tin' => '2001001000',
        ]);

        $this->followRedirects($response)->assertSee('Jane Moyo');
    }

    public function test_client_requires_a_name(): void
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->post('/clients', ['phone' => '0771234567']);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_client_can_be_updated(): void
    {
        $this->actingAsAuthenticatedUser();

        $client = Client::create(['name' => 'Old Name']);

        $response = $this->put("/clients/{$client->id}", [
            'name' => 'New Name',
            'contact_person' => 'Updated Contact',
            'phone' => '0771111111',
        ]);

        $response->assertRedirect(route('clients.show', $client));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'New Name',
            'contact_person' => 'Updated Contact',
        ]);
    }

    public function test_client_search_endpoint_returns_matching_json(): void
    {
        $this->actingAsAuthenticatedUser();

        Client::create(['name' => 'Bulawayo Drugstore', 'vat_number' => '111111111']);
        Client::create(['name' => 'Mutare Meds']);

        $response = $this->get('/clients/search?q=Bulawayo');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Bulawayo Drugstore']);
        $response->assertJsonMissing(['name' => 'Mutare Meds']);
    }
}
