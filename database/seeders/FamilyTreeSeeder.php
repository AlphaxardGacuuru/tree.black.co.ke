<?php

namespace Database\Seeders;

use App\Models\FamilyInvitation;
use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FamilyTreeSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', 'alphaxardgacuuru47@gmail.com')->firstOrFail();
        $father = User::query()->where('email', 'gacuuru@example.com')->firstOrFail();
        $mother = User::query()->where('email', 'augusta.gacuuru@example.com')->firstOrFail();
        $brother = User::query()->where('email', 'karenge.gacuuru@example.com')->firstOrFail();
        $aunt = User::query()->where('email', 'njeri.kariuki@example.com')->firstOrFail();
        $cousin = User::query()->where('email', 'sophia.kariuki@example.com')->firstOrFail();

        $tree = FamilyTree::query()->updateOrCreate(
            ['created_by' => $owner->id],
            ['name' => 'Gacuuru Family Demo Tree'],
        );

        $tree->members()->sync([
            $owner->id => ['role' => 'owner', 'joined_at' => now()->subDays(14)],
            $father->id => ['role' => 'member', 'joined_at' => now()->subDays(13)],
            $mother->id => ['role' => 'member', 'joined_at' => now()->subDays(12)],
            $brother->id => ['role' => 'member', 'joined_at' => now()->subDays(11)],
            $aunt->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $cousin->id => ['role' => 'member', 'joined_at' => now()->subDays(9)],
        ]);

        $relationships = [
            [$owner->id, $mother->id, 'child'],
            [$owner->id, $father->id, 'child'],
            [$mother->id, $owner->id, 'parent'],
            [$father->id, $owner->id, 'parent'],
            [$owner->id, $brother->id, 'sibling'],
            [$brother->id, $owner->id, 'sibling'],
            [$mother->id, $aunt->id, 'sibling'],
            [$aunt->id, $mother->id, 'sibling'],
            [$owner->id, $aunt->id, 'nephew'],
            [$aunt->id, $owner->id, 'aunt'],
            [$owner->id, $cousin->id, 'cousin'],
            [$cousin->id, $owner->id, 'cousin'],
        ];

        foreach ($relationships as [$userId, $relatedUserId, $relationshipType]) {
            FamilyRelationship::query()->firstOrCreate([
                'family_tree_id' => $tree->id,
                'user_id' => $userId,
                'related_user_id' => $relatedUserId,
                'relationship_type' => $relationshipType,
            ]);
        }
    }
}
