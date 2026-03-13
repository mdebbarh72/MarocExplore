<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_signup()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/signup', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'image' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token']);
        
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_signup_validation_fails()
    {
        $response = $this->postJson('/api/signup', []);
        $response->assertStatus(422);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'fail@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'fail@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->tokens);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson(['email' => $user->email]);
    }
}
