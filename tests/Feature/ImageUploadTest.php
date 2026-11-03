<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_an_article_image(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('upload.image'), [
            'image' => UploadedFile::fake()->image('recipe.jpg', 40, 40),
            'type' => 'article',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['url', 'path']);

        $path = $response->json('path');
        $this->assertIsString($path);
        $this->assertStringStartsWith('uploads/images/article/', $path);
        $this->assertFileExists(public_path($path));

        File::delete(public_path($path));
    }

    public function test_guest_cannot_upload_an_image(): void
    {
        $this->postJson(route('upload.image'), [
            'image' => UploadedFile::fake()->image('recipe.jpg'),
            'type' => 'article',
        ])->assertUnauthorized();
    }
}
