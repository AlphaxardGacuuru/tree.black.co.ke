<?php

namespace App\Services;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Http\Request;

class FamilyJoinService
{
    public function processInviteLink(Request $request, FamilyTree $familyTree, User $inviter, string $relationshipType): void
    {
        if (! $familyTree->members()->whereKey($inviter->id)->exists()) {
            abort(404);
        }

        $request->session()->put('family_join_context', [
            'inviter_id' => $inviter->id,
            'relationship_type' => $relationshipType,
            'inviter_name' => $inviter->name,
        ]);
    }
}
