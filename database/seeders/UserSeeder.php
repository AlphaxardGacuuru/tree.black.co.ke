<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create();

        // Alphaxard
        User::query()->updateOrCreate(
            ['email' => 'alphaxardgacuuru47@gmail.com'],
            [
                'name' => 'Alphaxard Gacuuru',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('alphaxardgacuuru47@gmail.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=13',
            ],
        );

        // Guka
        User::query()->updateOrCreate(
            ['email' => 'guka@example.com'],
            [
                'name' => 'Guka Gacuuru',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('guka@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Cucu
        User::query()->updateOrCreate(
            ['email' => 'cucu@example.com'],
            [
                'name' => 'Cucu Gacuuru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('cucu@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Gacuuru
        User::query()->updateOrCreate(
            ['email' => 'gacuuru@example.com'],
            [
                'name' => 'Gacuuru wa Karenge',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('gacuuru@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=11',
            ],
        );

        // Augusta
        User::query()->updateOrCreate(
            ['email' => 'augusta.gacuuru@example.com'],
            [
                'name' => 'Augusta Gacuuru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('augusta.gacuuru@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=47',
            ],
        );

        // Karenge
        User::query()->updateOrCreate(
            ['email' => 'karenge.gacuuru@example.com'],
            [
                'name' => 'Karenge Gacuuru',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('karenge.gacuuru@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Thoni
        User::query()->updateOrCreate(
            ['email' => 'thoni.gacuuru@example.com'],
            [
                'name' => 'Thoni Gacuuru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('thoni.gacuuru@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Ciku
        User::query()->updateOrCreate(
            ['email' => 'ciku.gacuuru@example.com'],
            [
                'name' => 'Ciku Gacuuru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('ciku.gacuuru@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Son One
        User::query()->updateOrCreate(
            ['email' => 'sonone@example.com'],
            [
                'name' => 'Son One',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('sonone@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Son Two
        User::query()->updateOrCreate(
            ['email' => 'sontwo@example.com'],
            [
                'name' => 'Son Two',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('sontwo@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Daughter One
        User::query()->updateOrCreate(
            ['email' => 'daughterone@example.com'],
            [
                'name' => 'Daughter One',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('daughterone@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Daughter Two
        User::query()->updateOrCreate(
            ['email' => 'daughtertwo@example.com'],
            [
                'name' => 'Daughter Two',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('daughtertwo@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Uncle Njuguna
        User::query()->updateOrCreate(
            ['email' => 'njuguna@example.com'],
            [
                'name' => 'Uncle Njuguna',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('njuguna@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Uncle Gatuha
        User::query()->updateOrCreate(
            ['email' => 'gatuha@example.com'],
            [
                'name' => 'Uncle Gatuha',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('gatuha@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Aunt Wanjiru
        User::query()->updateOrCreate(
            ['email' => 'wanjiru@example.com'],
            [
                'name' => 'Aunt Wanjiru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('wanjiru@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        // Aunt Njeri
        User::query()->updateOrCreate(
            ['email' => 'njeri@example.com'],
            [
                'name' => 'Aunt Njeri',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('njeri@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'sophia@example.com'],
            [
                'name' => 'Sophia',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('sophia@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=19',
            ],
        );
    }
}
