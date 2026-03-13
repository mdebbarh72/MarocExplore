<?php

namespace Tests\Feature;

use App\Models\Itenerary;
use App\Models\User;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_owner_can_add_destination_to_itinerary()
    {
        $itinerary = Itenerary::factory()->create(['user_id' => $this->user->id]);

        $payload = [
            'title' => 'Tangier',
            'address' => 'North Coast',
            'places' => ['Cape Spartel'],
            'activities' => ['Sightseeing'],
            'dishes' => ['Seafood Tagine']
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/itineraries/{$itinerary->id}/destinations", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'Tangier');
        
        $this->assertDatabaseHas('destinations', ['title' => 'Tangier', 'itenerary_id' => $itinerary->id]);
    }

    public function test_non_owner_cannot_add_destination()
    {
        $otherUser = User::factory()->create();
        $itinerary = Itenerary::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/itineraries/{$itinerary->id}/destinations", [
                'title' => 'Invalid Access',
                'address' => 'Secret'
            ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_update_destination()
    {
        $itinerary = Itenerary::factory()->create(['user_id' => $this->user->id]);
        $destination = Destination::factory()->create(['itenerary_id' => $itinerary->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/destinations/{$destination->id}", [
                'title' => 'Updated City Name'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated City Name');
        
        $this->assertDatabaseHas('destinations', ['id' => $destination->id, 'title' => 'Updated City Name']);
    }

    public function test_owner_can_delete_destination()
    {
        $itinerary = Itenerary::factory()->create(['user_id' => $this->user->id]);
        $destination = Destination::factory()->create(['itenerary_id' => $itinerary->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/destinations/{$destination->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }

    public function test_non_owner_cannot_delete_destination()
    {
        $otherUser = User::factory()->create();
        $itinerary = Itenerary::factory()->create(['user_id' => $otherUser->id]);
        $destination = Destination::factory()->create(['itenerary_id' => $itinerary->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/destinations/{$destination->id}");

        $response->assertStatus(403);
    }
}
