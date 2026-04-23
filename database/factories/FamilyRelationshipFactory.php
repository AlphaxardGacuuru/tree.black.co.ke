<?php

namespace Database\Factories;

use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyRelationship>
 */
class FamilyRelationshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'family_tree_id' => FamilyTree::factory(),
            'user_id' => User::factory(),
            'related_user_id' => User::factory(),
            'relationship_type' => fake()->randomElement(['sibling', 'parent', 'child', 'cousin']),
        ];
    }
}
