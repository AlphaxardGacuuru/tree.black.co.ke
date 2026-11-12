<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Services\SocialiteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class ProfileAvatarUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_user_can_upload_a_profile_picture(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('me.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('saved', true)
            ->assertJsonPath('message', 'Profile picture updated.');

        $path = $user
            ->refresh()
            ->getRawOriginal('avatar');

        $this->assertStringStartsWith(User::AVATAR_UPLOAD_DIRECTORY.'/', $path);
        $this->assertTrue($user->hasUploadedAvatar());
        $this->assertSame('/storage/'.$path, $user->avatar);
        $response->assertJsonPath('avatar', '/storage/'.$path);
        Storage::disk('public')
            ->assertExists($path);
    }

    public function test_the_shared_auth_user_gets_the_uploaded_picture_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('me.png'),
            ])
            ->assertOk();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('auth.user.avatar', $user->refresh()->avatar)
            );
    }

    public function test_users_without_a_picture_have_a_null_avatar(): void
    {
        $user = User::factory()->create(['avatar' => null]);

        $this->assertNull($user->avatar);
        $this->assertFalse($user->hasUploadedAvatar());
    }

    public function test_uploading_a_new_picture_deletes_the_previous_upload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('first.jpg'),
            ])
            ->assertOk();

        $first = $user
            ->refresh()
            ->getRawOriginal('avatar');

        $this->actingAs($user)
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('second.png'),
            ])
            ->assertOk();

        Storage::disk('public')
            ->assertMissing($first)
            ->assertExists($user->refresh()->getRawOriginal('avatar'));
    }

    public function test_a_google_picture_is_not_deleted_when_uploading(): void
    {
        $user = User::factory()->create([
            'avatar' => 'https://lh3.googleusercontent.com/a/avatar',
        ]);

        $this->assertFalse($user->hasUploadedAvatar());

        $this->actingAs($user)
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('me.jpg'),
            ])
            ->assertOk();

        $this->assertTrue($user->refresh()->hasUploadedAvatar());
    }

    public function test_the_upload_must_be_a_small_enough_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('avatar');

        $this->actingAs($user)
            ->postJson(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()
                    ->image('huge.jpg')
                    ->size(User::MAX_AVATAR_KB + 1),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'avatar' => 'Your profile picture must be '.(User::MAX_AVATAR_KB / 1024).' MB or smaller.',
            ]);

        $this->actingAs($user)
            ->postJson(route('profile.avatar.update'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('avatar');

        $this->assertFalse($user->refresh()->hasUploadedAvatar());
    }

    public function test_guests_cannot_upload_a_profile_picture(): void
    {
        $this->postJson(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ])
            ->assertUnauthorized();
    }

    public function test_google_sign_in_keeps_an_uploaded_picture(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('me.jpg'),
            ])
            ->assertOk();

        $uploaded = $user
            ->refresh()
            ->getRawOriginal('avatar');

        $googleUser = (new SocialiteUser)
            ->setRaw([])
            ->map([
                'id' => 'google-1',
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => 'https://lh3.googleusercontent.com/a/new-avatar',
            ]);

        app(SocialiteService::class)
            ->resolveUser($googleUser);

        $this->assertSame($uploaded, $user->refresh()->getRawOriginal('avatar'));
        $this->assertSame('google-1', $user->google_id);
    }

    public function test_google_sign_in_still_updates_a_google_picture(): void
    {
        $user = User::factory()->create([
            'avatar' => 'https://lh3.googleusercontent.com/a/old-avatar',
        ]);

        $googleUser = (new SocialiteUser)
            ->setRaw([])
            ->map([
                'id' => 'google-1',
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => 'https://lh3.googleusercontent.com/a/new-avatar',
            ]);

        app(SocialiteService::class)
            ->resolveUser($googleUser);

        $this->assertSame('https://lh3.googleusercontent.com/a/new-avatar', $user->refresh()->avatar);
    }
}
