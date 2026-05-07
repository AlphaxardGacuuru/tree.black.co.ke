<?php

namespace Database\Seeders;

use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Database\Seeder;

class FamilyTreeSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'alphaxardgacuuru47@gmail.com')->firstOrFail();

        $grandFather = User::where('email', 'guka@example.com')->firstOrFail();
        $grandMother = User::where('email', 'cucu@example.com')->firstOrFail();

        $father = User::where('email', 'gacuuru@example.com')->firstOrFail();
        $mother = User::where('email', 'augusta.gacuuru@example.com')->firstOrFail();

        $brother = User::where('email', 'karenge.gacuuru@example.com')->firstOrFail();
        $bigSister = User::where('email', 'thoni.gacuuru@example.com')->firstOrFail();
        $smallSister = User::where('email', 'ciku.gacuuru@example.com')->firstOrFail();

        $nathan = User::where('email', 'nathan.muhandi@example.com')->firstOrFail();
        $nadia = User::where('email', 'nadia.muhandi@example.com')->firstOrFail();
        $nayla = User::where('email', 'nayla.muhandi@example.com')->firstOrFail();

        $ivy = User::where('email', 'ivy@example.com')->firstOrFail();

        $sonOne = User::where('email', 'sonone@example.com')->firstOrFail();
        $sonTwo = User::where('email', 'sontwo@example.com')->firstOrFail();
        $daughterOne = User::where('email', 'daughterone@example.com')->firstOrFail();
        $daughterTwo = User::where('email', 'daughtertwo@example.com')->firstOrFail();

        $uncleNjuguna = User::where('email', 'njuguna@example.com')->firstOrFail();
        $uncleGatuha = User::where('email', 'gatuha@example.com')->firstOrFail();

        $auntWanjiru = User::where('email', 'wanjiru@example.com')->firstOrFail();
        $wambui = User::where('email', 'wambui.wanjiru@example.com')->firstOrFail();
        $cikuWanjiru = User::where('email', 'ciku.wanjiru@example.com')->firstOrFail();
        $hillaryWanjiru = User::where('email', 'hillary.wanjiru@example.com')->firstOrFail();
        $njorogeWanjiru = User::where('email', 'njoroge.wanjiru@example.com')->firstOrFail();

        $auntNjeri = User::where('email', 'njeri@example.com')->firstOrFail();

        $tree = FamilyTree::updateOrCreate(
            ['created_by' => $owner->id],
            ['name' => 'Gacuuru Family Tree'],
        );

        $tree->members()->sync([
            $owner->id => ['role' => 'owner', 'joined_at' => now()->subDays(14)],
            
            $grandFather->id => ['role' => 'member', 'joined_at' => now()->subDays(14)],
            $grandMother->id => ['role' => 'member', 'joined_at' => now()->subDays(14)],
            
            $father->id => ['role' => 'member', 'joined_at' => now()->subDays(13)],
            $mother->id => ['role' => 'member', 'joined_at' => now()->subDays(12)],
            
            $brother->id => ['role' => 'member', 'joined_at' => now()->subDays(11)],
            $bigSister->id => ['role' => 'member', 'joined_at' => now()->subDays(11)],
            $smallSister->id => ['role' => 'member', 'joined_at' => now()->subDays(11)],
            
            $nathan->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $nadia->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $nayla->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            
            $ivy->id => ['role' => 'member', 'joined_at' => now()->subDays(14)],

            $sonOne->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $sonTwo->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $daughterOne->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $daughterTwo->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],

            $uncleNjuguna->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $uncleGatuha->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],

            $auntWanjiru->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $wambui->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $cikuWanjiru->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $hillaryWanjiru->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            $njorogeWanjiru->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
            
            $auntNjeri->id => ['role' => 'member', 'joined_at' => now()->subDays(10)],
        ]);

        $relationships = [
            [$grandFather->id, $father->id, 'son'],
            [$grandMother->id, $father->id, 'daughter'],
            [$father->id, $grandFather->id, 'father'],
            [$father->id, $grandMother->id, 'mother'],

            [$owner->id, $father->id, 'father'],
            [$owner->id, $mother->id, 'mother'],
            [$mother->id, $owner->id, 'son'],
            [$father->id, $owner->id, 'son'],

            [$owner->id, $brother->id, 'brother'],
            [$owner->id, $bigSister->id, 'sister'],
            [$owner->id, $smallSister->id, 'sister'],
            [$brother->id, $owner->id, 'brother'],
            [$bigSister->id, $owner->id, 'sister'],
            [$smallSister->id, $owner->id, 'sister'],

            [$ivy->id, $owner->id, 'wife'],
            [$owner->id, $ivy->id, 'husband'],

            [$smallSister->id, $nathan->id, 'son'],
            [$smallSister->id, $nadia->id, 'daughter'],
            [$smallSister->id, $nayla->id, 'daughter'],
            [$nathan->id, $smallSister->id, 'mother'],
            [$nadia->id, $smallSister->id, 'mother'],
            [$nayla->id, $smallSister->id, 'mother'],

            [$owner->id, $sonOne->id, 'son'],
            [$owner->id, $sonTwo->id, 'son'],
            [$owner->id, $daughterOne->id, 'daughter'],
            [$owner->id, $daughterTwo->id, 'daughter'],
            [$sonOne->id, $owner->id, 'father'],
            [$sonTwo->id, $owner->id, 'father'],
            [$daughterOne->id, $owner->id, 'mother'],
            [$daughterTwo->id, $owner->id, 'mother'],
            
            [$father->id, $uncleNjuguna->id, 'brother'],
            [$uncleNjuguna->id, $father->id, 'brother'],

            [$mother->id, $uncleGatuha->id, 'brother'],
            [$mother->id, $auntNjeri->id, 'sister'],
            [$mother->id, $auntWanjiru->id, 'sister'],
            [$uncleGatuha->id, $mother->id, 'brother'],
            [$auntNjeri->id, $mother->id, 'sister'],
            [$auntWanjiru->id, $mother->id, 'sister'],

            [$auntWanjiru->id, $wambui->id, 'daughter'],
            [$auntWanjiru->id, $cikuWanjiru->id, 'daughter'],
            [$auntWanjiru->id, $hillaryWanjiru->id, 'daughter'],
            [$auntWanjiru->id, $njorogeWanjiru->id, 'son'],
            [$wambui->id, $auntWanjiru->id, 'mother'],
            [$cikuWanjiru->id, $auntWanjiru->id, 'mother'],
            [$hillaryWanjiru->id, $auntWanjiru->id, 'mother'],
            [$njorogeWanjiru->id, $auntWanjiru->id, 'mother'],
        ];

        foreach ($relationships as [$userId, $relatedUserId, $relationshipType]) {
            FamilyRelationship::updateOrCreate([
                'family_tree_id' => $tree->id,
                'user_id' => $userId,
                'related_user_id' => $relatedUserId,
                'relationship_type' => $relationshipType,
            ]);
        }
    }
}
