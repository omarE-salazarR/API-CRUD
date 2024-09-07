<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Challenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ChallengeControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $email = 'testuser@example.com';
    protected $password = 'password123';
    
    /**
     * Set up the environment for the tests.
     */
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
     * Test the endpoint for creating a challenge.
     *
     * @return void
     */
    public function test_create_challenge_endpoint()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->postJson('/api/challenges', [
                             'type' => 'manual',
                             'title' => 'New Challenge',
                             'description' => 'Challenge Description',
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
     * Test the endpoint for listing challenges.
     *
     * @return void
     */
    public function test_list_challenges_endpoint()
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
     * Test the endpoint for getting a challenge by ID.
     *
     * @return void
     */
    public function test_get_challenge_by_id_endpoint()
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
     * Test the endpoint for updating a challenge.
     *
     * @return void
     */
    public function test_update_challenge_endpoint()
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
     * Test the endpoint for deleting a challenge.
     *
     * @return void
     */
    public function test_delete_challenge_endpoint()
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
