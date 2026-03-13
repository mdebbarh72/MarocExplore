<?php

namespace Tests\Feature;

use App\Models\category;
use App\Models\Itenerary;
use App\Models\User;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItineraryTest extends TestCase
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

    public function test_can_list_itineraries()
    {
        Itenerary::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/itineraries');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_itineraries_by_title()
    {
        Itenerary::factory()->create(['title' => 'Marrakech Expedition']);
        Itenerary::factory()->create(['title' => 'Desert Trip']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/itineraries?search=Marrakech');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Marrakech Expedition');
    }

    public function test_can_filter_itineraries_by_destination()
    {
        $itinerary = Itenerary::factory()->create();
        Destination::factory()->create([
            'itenerary_id' => $itinerary->id,
            'title' => 'Ouzoud Falls'
        ]);
        
        Itenerary::factory()->create(); // Another itinerary

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/itineraries?destination=Ouzoud');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_itinerary()
    {
        Storage::fake('public');
        $cat = category::factory()->create();
        $file = UploadedFile::fake()->image('trip.jpg');

        $payload = [
            'title' => 'New Adventure',
            'category' => $cat->id,
            'image' => $file,
            'destinations' => [
                [
                    'title' => 'Fez Medina',
                    'address' => 'Old Fez',
                    'places' => ['Al-Attarine Madrasa'],
                    'activities' => ['Leather workshop'],
                    'dishes' => ['Fes Pastilla']
                ],
                [
                    'title' => 'Chefchaouen',
                    'address' => 'Rif Mountains',
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/itineraries', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('title', 'New Adventure');
        
        $this->assertDatabaseHas('iteneraries', ['title' => 'New Adventure']);
        $this->assertDatabaseHas('destinations', ['title' => 'Fez Medina']);
    }

    public function test_can_copy_itinerary()
    {
        $originalOwner = User::factory()->create();
        $itinerary = Itenerary::factory()->create(['user_id' => $originalOwner->id]);
        Destination::factory()->count(2)->create(['itenerary_id' => $itinerary->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/itineraries/{$itinerary->id}/copy");

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('iteneraries', [
            'user_id' => $this->user->id,
            'title' => $itinerary->title,
            'status' => 'pending'
        ]);
        
        $newItineraryId = $response->json('id');
        $this->assertDatabaseCount('destinations', 4); // 2 original + 2 cloned
    }

    public function test_can_get_my_itineraries()
    {
        Itenerary::factory()->count(2)->create(['user_id' => $this->user->id]);
        Itenerary::factory()->create(); // Another user's

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/itineraries/my');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
