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
            ->map(fn($relationship) => $this->relationship($relationship))
            ->groupBy('relationshipType');

        $father = $familyTree->nodes['father'][0] ?? null;

        if ($father) {
            $paternalUncles = FamilyRelationship::where('user_id', $father['relatedUserId'])
                ->where('relationship_type', 'brother')
                ->get()
                ->map(fn($relationship) => $this->relationship($relationship));

            $paternalAunts = FamilyRelationship::where('user_id', $father['relatedUserId'])
                ->where('relationship_type', 'sister')
                ->get()
                ->map(fn($relationship) => $this->relationship($relationship));

            $familyTree->nodes['paternalUncles'] = $paternalUncles;
            $familyTree->nodes['paternalAunts'] = $paternalAunts;
        }

        $mother = $familyTree->nodes['mother'][0] ?? null;

        if ($mother) {
            $maternalUncles = FamilyRelationship::where('user_id', $mother['relatedUserId'])
                ->where('relationship_type', 'brother')
                ->get()
                ->map(fn($relationship) => $this->relationship($relationship));

            $maternalAunts = FamilyRelationship::where('user_id', $mother['relatedUserId'])
                ->where('relationship_type', 'sister')
                ->get()
                ->map(fn($relationship) => $this->relationship($relationship));

            $familyTree->nodes['maternalUncles'] = $maternalUncles;
            $familyTree->nodes['maternalAunts'] = $maternalAunts;
        }

        return $familyTree;
    }

    protected function relationship(FamilyRelationship $relationship): array
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
