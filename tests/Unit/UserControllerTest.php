<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $token;
    protected $email = 'testuser@example.com';
    protected $password = 'password123';
    /**
     * Test user registration.
     *
     * @return void
     */
    public function test_user_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'response' => 'Usuario creado',
                     'errors' => [],
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => $this->email,
        ]);
    }

    /**
     * Test user login and token generation.
     *
     * @return void
     */
    public function test_user_login()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'token',
                 ]);
    }

    /**
     * Test creating a user with a token.
     *
     * @return void
     */
    public function test_create_user_with_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/users', [
                             'type' => 'manual',
                             'name' => 'New User',
                             'email' => 'newuser@example.com',
                             'password' => 'password123',
                         ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'response' => 'Usuario creado', 
                     'errors' => []
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test listing users with a token.
     *
     * @return void
     */
    public function test_list_users_with_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'email' => $user->email,
                 ]);
    }

    /**
     * Test getting a user by ID with a token.
     *
     * @return void
     */
    public function test_get_user_by_id_with_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'email' => $user->email,
                 ]);
    }

    /**
     * Test updating a user with a token.
     *
     * @return void
     */
    public function test_update_user_with_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->putJson('/api/users/' . $user->id, [
                             'name' => 'Updated Name',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'Updated Name',
                 ]);
    }

    /**
     * Test deleting a user with a token.
     *
     * @return void
     */
    public function test_delete_user_with_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Usuario eliminado con éxito',
                 ]);

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
        ]);
    }
}
