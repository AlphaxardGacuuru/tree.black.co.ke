<?php

namespace Database\Factories;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyTree>
 */
class FamilyTreeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->lastName().' Family',
            'created_by' => User::factory(),
        ];
    }
}
