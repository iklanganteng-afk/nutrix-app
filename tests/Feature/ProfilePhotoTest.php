<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_brand_stays_in_the_workspace_and_exposes_profile_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="' . route('dashboard') . '"', false)
            ->assertSee('id="profilePhotoInput"', false)
            ->assertSee('action="' . route('profile.photo.update') . '"', false);
    }

    public function test_authenticated_user_can_save_a_profile_photo_to_their_account(): void
    {
        $user = User::factory()->create();
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/npgAAAAASUVORK5CYII=', true);

        $this->actingAs($user)
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->createWithContent('avatar.png', $image),
            ])
            ->assertRedirect()
            ->assertSessionHas('profile_photo_saved');

        $savedUser = $user->fresh();
        $this->assertSame('image/png', $savedUser->profile_photo_mime);
        $this->assertSame($image, base64_decode(substr($savedUser->profile_photo_url, strlen('data:image/png;base64,'))));
    }

    public function test_profile_photo_upload_rejects_non_image_files(): void
    {
        $this->actingAs(User::factory()->create())
            ->from('/dashboard')
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->create('notes.txt', 2, 'text/plain'),
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors('photo');
    }
}