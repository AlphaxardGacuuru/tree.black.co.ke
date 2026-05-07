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
                'avatar' => 'https://i.pravatar.cc/300?img=13',
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
                'avatar' => 'https://i.pravatar.cc/300?img=56',
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
                'avatar' => 'https://i.pravatar.cc/300?img=57',
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
                'avatar' => 'https://i.pravatar.cc/300?img=58',
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
                'avatar' => 'https://i.pravatar.cc/300?img=59',
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
                'avatar' => 'https://i.pravatar.cc/300?img=60',
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
                'avatar' => 'https://i.pravatar.cc/300?img=61',
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
                'avatar' => 'https://i.pravatar.cc/300?img=62',
            ],
        );

        // Nathan Muhandi
        User::query()->updateOrCreate(
            ['email' => 'nathan.muhandi@example.com'],
            [
                'name' => 'Nathan Muhandi',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('nathan.muhandi@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=63',
            ],
        );

        // Nadia Muhandi
        User::query()->updateOrCreate(
            ['email' => 'nadia.muhandi@example.com'],
            [
                'name' => 'Nadia Muhandi',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('nadia.muhandi@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=64',
            ],
        );

        // Nayla Muhandi
        User::query()->updateOrCreate(
            ['email' => 'nayla.muhandi@example.com'],
            [
                'name' => 'Nayla Muhandi',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('nayla.muhandi@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=65',
            ],
        );

        // Ivy
        User::query()->updateOrCreate(
            ['email' => 'ivy@example.com'],
            [
                'name' => 'Ivy Njoroge',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('ivy@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=63',
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
                'avatar' => 'https://i.pravatar.cc/300?img=32',
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
                'avatar' => 'https://i.pravatar.cc/300?img=33',
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
                'avatar' => 'https://i.pravatar.cc/300?img=34',
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
                'avatar' => 'https://i.pravatar.cc/300?img=35',
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
                'avatar' => 'https://i.pravatar.cc/300?img=20',
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
                'avatar' => 'https://i.pravatar.cc/300?img=21',
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
                'avatar' => 'https://i.pravatar.cc/300?img=22',
            ],
        );

        // Wambui Wanjiru
        User::query()->updateOrCreate(
            ['email' => 'wambui.wanjiru@example.com'],
            [
                'name' => 'Wambui Wanjiru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('wambui.wanjiru@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=23',
            ],
        );

        // Ciku Wanjiru
        User::query()->updateOrCreate(
            ['email' => 'ciku.wanjiru@example.com'],
            [
                'name' => 'Ciku Wanjiru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('ciku.wanjiru@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=24',
            ],
        );

        // Hillary Wanjiru
        User::query()->updateOrCreate(
            ['email' => 'hillary.wanjiru@example.com'],
            [
                'name' => 'Hillary Wanjiru',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('hillary.wanjiru@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=25',
            ],
        );

        // Njoroge Wanjiru
        User::query()->updateOrCreate(
            ['email' => 'njoroge.wanjiru@example.com'],
            [
                'name' => 'Njoroge Wanjiru',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('njoroge.wanjiru@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=26',
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
                'avatar' => 'https://i.pravatar.cc/300?img=27',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'sophia@example.com'],
            [
                'name' => 'Sophia',
                'gender' => 'female',
                'email_verified_at' => now(),
                'password' => Hash::make('sophia@example.com'),
                'avatar' => 'https://i.pravatar.cc/300?img=19',
            ],
        );
    }
}
