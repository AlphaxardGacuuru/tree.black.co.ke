<?php

namespace App\Http\Services;

use App\Http\Resources\FamilyRelationshipResource;
use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Models\User;

class FamilyTreeService
{
    public function show(string $id): FamilyTree
    {
        $familyTree = FamilyTree::with(['members', 'relationships'])->findOrFail($id);

        $familyTree->nodes = $familyTree
            ->relationships()
            ->where('user_id', auth()->user()->id)
            ->get()
            ->map(fn($relationship) => $this->relationshipMap($relationship))
            ->groupBy('relationshipType');

        $this->getPaternalUnclesAndAunts($familyTree);
        $this->getMaternalUnclesAndAunts($familyTree);

        $this->getPaternalCousins($familyTree);
        $this->getMaternalCousins($familyTree);

        $this->getPaternalNephewsAndNieces($familyTree);
        $this->getMaternalNephewsAndNieces($familyTree);

        return $familyTree;
    }

    private function getPaternalUnclesAndAunts(FamilyTree $familyTree): void
    {
        $father = $familyTree->nodes['father'][0] ?? null;

        if ($father) {
            $paternalUncles = FamilyRelationship::where('user_id', $father['relatedUserId'])
                ->where('relationship_type', 'brother')
                ->get()
                ->map(fn($relationship) => $this->relationshipMap($relationship));

            $paternalAunts = FamilyRelationship::where('user_id', $father['relatedUserId'])
                ->where('relationship_type', 'sister')
                ->get()
                ->map(fn($relationship) => $this->relationshipMap($relationship));

            $familyTree->nodes['paternalUncles'] = $paternalUncles;
            $familyTree->nodes['paternalAunts'] = $paternalAunts;
        }
    }

    private function getMaternalUnclesAndAunts(FamilyTree $familyTree): void
    {
        $mother = $familyTree->nodes['mother'][0] ?? null;

        if ($mother) {
            $maternalUncles = FamilyRelationship::where('user_id', $mother['relatedUserId'])
                ->where('relationship_type', 'brother')
                ->get()
                ->map(fn($relationship) => $this->relationshipMap($relationship));

            $maternalAunts = FamilyRelationship::where('user_id', $mother['relatedUserId'])
                ->where('relationship_type', 'sister')
                ->get()
                ->map(fn($relationship) => $this->relationshipMap($relationship));

            $familyTree->nodes['maternalUncles'] = $maternalUncles;
            $familyTree->nodes['maternalAunts'] = $maternalAunts;
        }
    }

    protected function getPaternalCousins(FamilyTree $familyTree): void
    {
        $paternalUncles = collect($familyTree->nodes['paternalUncles'] ?? []);
        $paternalAunts = collect($familyTree->nodes['paternalAunts'] ?? []);

        $paternalCousins = collect();

        $paternalUncles
            ->concat($paternalAunts)
            ->each(function ($uncle) use (&$paternalCousins) {
                $cousins = FamilyRelationship::where('user_id', $uncle['relatedUserId'])
                    ->whereIn('relationship_type', ['son', 'daughter'])
                    ->get()
                    ->map(fn($relationship) => $this->relationshipMap($relationship));

                $paternalCousins = $paternalCousins->merge($cousins);
            });

        $familyTree->nodes['paternalCousins'] = $paternalCousins;
    }

    protected function getMaternalCousins(FamilyTree $familyTree): void
    {
        $maternalUncles = collect($familyTree->nodes['maternalUncles'] ?? []);
        $maternalAunts = collect($familyTree->nodes['maternalAunts'] ?? []);

        $maternalCousins = collect();

        $maternalUncles
            ->concat($maternalAunts)
            ->each(function ($uncle) use (&$maternalCousins) {
                $cousins = FamilyRelationship::where('user_id', $uncle['relatedUserId'])
                    ->whereIn('relationship_type', ['son', 'daughter'])
                    ->get()
                    ->map(fn($relationship) => $this->relationshipMap($relationship));

                $maternalCousins = $maternalCousins->merge($cousins);
            });

        $familyTree->nodes['maternalCousins'] = $maternalCousins;
    }

    protected function getPaternalNephewsAndNieces(FamilyTree $familyTree): void
    {
        $brothers = $familyTree->nodes['brother'] ?? [];

        $nephewsAndNieces = collect();

        collect($brothers)->each(function ($brother) use (&$nephewsAndNieces) {
            $children = FamilyRelationship::where('user_id', $brother['relatedUserId'])
                ->whereIn('relationship_type', ['son', 'daughter'])
                ->get()
                ->map(fn($relationship) => $this->relationshipMap($relationship));

            $nephewsAndNieces = $nephewsAndNieces->merge($children);
        });

        $familyTree->nodes['paternalNephewsAndNieces'] = $nephewsAndNieces;
    }

    protected function getMaternalNephewsAndNieces(FamilyTree $familyTree): void
    {
        $sisters = $familyTree->nodes['sister'] ?? [];

        $nephewsAndNieces = collect();

        collect($sisters)->each(function ($sister) use (&$nephewsAndNieces) {
            $children = FamilyRelationship::where('user_id', $sister['relatedUserId'])
                ->whereIn('relationship_type', ['son', 'daughter'])
                ->get()
                ->map(fn($relationship) => $this->relationshipMap($relationship));

            $nephewsAndNieces = $nephewsAndNieces->merge($children);
        });

        $familyTree->nodes['maternalNephewsAndNieces'] = $nephewsAndNieces;
    }

    protected function relationshipMap(FamilyRelationship $relationship): array
    {
        return [
            'id' => $relationship->id,
            'userId' => $relationship->user_id,
            'relatedUserId' => $relationship->related_user_id,
            'relationshipType' => $relationship->relationship_type,
            'name' => $relationship->relatedUser->name,
            'avatar' => $relationship->relatedUser->avatar,
        ];
    }
}
