<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialiteService
{
    public function resolveUser(SocialiteUser $googleUser): User
    {
        $avatarUrl = $googleUser->getAvatar();

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $attributes = [];

            if ($user->google_id !== $googleUser->getId()) {
                $attributes['google_id'] = $googleUser->getId();
            }

            if ($user->email_verified_at === null) {
                $attributes['email_verified_at'] = now();
            }

            if (filled($avatarUrl) && $user->avatar !== $avatarUrl) {
                $attributes['avatar'] = $avatarUrl;
            }

            if ($attributes !== []) {
                $user->forceFill($attributes)->save();
            }

            return $user;
        }

        return User::create([
            'name' => $googleUser->getName() ?: 'Google User',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $avatarUrl,
            'email_verified_at' => now(),
            'password' => Str::random(40),
        ]);
    }
}
