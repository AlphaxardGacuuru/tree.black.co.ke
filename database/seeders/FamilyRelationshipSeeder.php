<?php

namespace Database\Seeders;

use App\Models\FamilyRelationship;
use App\Models\User;
use Illuminate\Database\Seeder;

class FamilyRelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', 'alphaxardgacuuru47@gmail.com')->firstOrFail();

        $grandFather = User::query()->where('email', 'guka@example.com')->firstOrFail();
        $grandMother = User::query()->where('email', 'cucu@example.com')->firstOrFail();

        $father = User::query()->where('email', 'gacuuru@example.com')->firstOrFail();
        $mother = User::query()->where('email', 'augusta.gacuuru@example.com')->firstOrFail();

        $brother = User::query()->where('email', 'karenge.gacuuru@example.com')->firstOrFail();
        $bigSister = User::query()->where('email', 'thoni.gacuuru@example.com')->firstOrFail();
        $smallSister = User::query()->where('email', 'ciku.gacuuru@example.com')->firstOrFail();

        $nathan = User::query()->where('email', 'nathan.muhandi@example.com')->firstOrFail();
        $nadia = User::query()->where('email', 'nadia.muhandi@example.com')->firstOrFail();
        $nayla = User::query()->where('email', 'nayla.muhandi@example.com')->firstOrFail();

        $ivy = User::query()->where('email', 'ivy@example.com')->firstOrFail();

        $sonOne = User::query()->where('email', 'sonone@example.com')->firstOrFail();
        $sonTwo = User::query()->where('email', 'sontwo@example.com')->firstOrFail();
        $daughterOne = User::query()->where('email', 'daughterone@example.com')->firstOrFail();
        $daughterTwo = User::query()->where('email', 'daughtertwo@example.com')->firstOrFail();

        $uncleNjuguna = User::query()->where('email', 'njuguna@example.com')->firstOrFail();
        $uncleGatuha = User::query()->where('email', 'gatuha@example.com')->firstOrFail();

        $auntWanjiru = User::query()->where('email', 'wanjiru@example.com')->firstOrFail();
        $wambui = User::query()->where('email', 'wambui.wanjiru@example.com')->firstOrFail();
        $cikuWanjiru = User::query()->where('email', 'ciku.wanjiru@example.com')->firstOrFail();
        $hillaryWanjiru = User::query()->where('email', 'hillary.wanjiru@example.com')->firstOrFail();
        $njorogeWanjiru = User::query()->where('email', 'njoroge.wanjiru@example.com')->firstOrFail();

        $auntNjeri = User::query()->where('email', 'njeri@example.com')->firstOrFail();

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
                'user_id' => $userId,
                'related_user_id' => $relatedUserId,
                'relationship_type' => $relationshipType,
            ]);
        }
    }
}
