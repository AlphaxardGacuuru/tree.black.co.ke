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
                'gender' => 'male',
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
            ['email' => 'thoni@example.com'],
            [
                'name' => 'Thoni Gacuuru',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('thoni@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Ciku
        User::query()->updateOrCreate(
            ['email' => 'ciku@example.com'],
            [
                'name' => 'Ciku Gacuuru',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('ciku@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=56',
            ],
        );

        // Aunt Njeri
        User::query()->updateOrCreate(
            ['email' => 'njeri.kariuki@example.com'],
            [
                'name' => 'Njeri Kariuki',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('njeri.kariuki@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=32',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'sophia.kariuki@example.com'],
            [
                'name' => 'Sophia Kariuki',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('sophia.kariuki@example.com'),
                'avatar_url' => 'https://i.pravatar.cc/300?img=19',
            ],
        );
    }
}
