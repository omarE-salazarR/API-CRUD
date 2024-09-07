<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class VideoControllerTest extends TestCase
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
     * Test video creation.
     *
     * @return void
     */
    public function test_create_video()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->postJson('/api/videos', [
                             'type' => 'manual',
                             'title' => 'Test Video',
                             'description' => 'Description of Test Video',
                             'url' => 'http://example.com/video',
                         ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'response' => 'Video creado',
                     'errors' => []
                 ]);

        $this->assertDatabaseHas('videos', [
            'title' => 'Test Video',
        ]);
    }

    /**
     * Test listing videos.
     *
     * @return void
     */
    public function test_list_videos()
    {
        $video = Video::create([
            'title' => 'Existing Video',
            'description' => 'Description of Existing Video',
            'url' => 'http://example.com/existing-video',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->getJson('/api/videos');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'title' => $video->title,
                 ]);
    }

    /**
     * Test getting a video by ID.
     *
     * @return void
     */
    public function test_get_video_by_id()
    {
        $video = Video::create([
            'title' => 'Existing Video',
            'description' => 'Description of Existing Video',
            'url' => 'http://example.com/existing-video',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->getJson('/api/videos/' . $video->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'title' => $video->title,
                 ]);
    }

    /**
     * Test updating a video.
     *
     * @return void
     */
    public function test_update_video()
    {
        $video = Video::create([
            'title' => 'Old Title',
            'description' => 'Old Description',
            'url' => 'http://example.com/old-video',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->putJson('/api/videos/' . $video->id, [
                             'title' => 'Updated Title',
                             'description' => 'Updated Description',
                             'url' => 'http://example.com/updated-video',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'title' => 'Updated Title',
                 ]);
    }

    /**
     * Test deleting a video.
     *
     * @return void
     */
    public function test_delete_video()
    {
        $video = Video::create([
            'title' => 'Video to Delete',
            'description' => 'Description of Video to Delete',
            'url' => 'http://example.com/video-to-delete',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
                         ->deleteJson('/api/videos/' . $video->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Video eliminado con éxito',
                 ]);

        $this->assertDatabaseMissing('videos', [
            'title' => $video->title,
        ]);
    }
}
