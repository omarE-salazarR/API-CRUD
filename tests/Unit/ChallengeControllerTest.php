<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Challenge; // Asegúrate de tener un modelo Challenge
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ChallengeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $email = 'testuser@example.com';
    protected $password = 'password123';
    
    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario y obtener un token para pruebas
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $this->token = $response->json('token');
    }

    /**
     * Test creating a challenge with a token.
     *
     * @return void
     */
    public function test_create_challenge_with_token()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->postJson('/api/challenges', [
                            'type' => 'manual',
                            'title' => 'New Challenge',
                            'description' => 'Challenge Description'
                         ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'response' => 'Challenge creado',
                     'errors' => []
                 ]);

        $this->assertDatabaseHas('challenges', [
            'title' => 'New Challenge',
        ]);
    }

    /**
     * Test listing challenges with a token.
     *
     * @return void
     */
    public function test_list_challenges_with_token()
    {
        $challenge = Challenge::create([
            'title' => 'Existing Challenge',
            'description' => 'Existing Challenge Description',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->getJson('/api/challenges');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => $challenge->title,
                 ]);
    }

    /**
     * Test getting a challenge by ID with a token.
     *
     * @return void
     */
    public function test_get_challenge_by_id_with_token()
    {
        $challenge = Challenge::create([
            'title' => 'Existing Challenge',
            'description' => 'Existing Challenge Description',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->getJson('/api/challenges/' . $challenge->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'title' => $challenge->title,
                 ]);
    }

    /**
     * Test updating a challenge with a token.
     *
     * @return void
     */
    public function test_update_challenge_with_token()
    {
        $challenge = Challenge::create([
            'title' => 'Old Title',
            'description' => 'Old Description',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->putJson('/api/challenges/' . $challenge->id, [
                             'title' => 'Updated Title',
                             'description' => 'Updated Description',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'title' => 'Updated Title',
                 ]);
    }

    /**
     * Test deleting a challenge with a token.
     *
     * @return void
     */
    public function test_delete_challenge_with_token()
    {
        $challenge = Challenge::create([
            'title' => 'Challenge to Delete',
            'description' => 'Description of Challenge to Delete',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->deleteJson('/api/challenges/' . $challenge->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Challenge eliminado con éxito',
                 ]);

        $this->assertDatabaseMissing('challenges', [
            'title' => $challenge->title,
        ]);
    }
}
