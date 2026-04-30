<?php

namespace App\Http\Controllers;

use App\Models\FamilyTree;
use App\Models\User;
use App\Services\FamilyJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FamilyJoinController extends Controller
{
    public function __construct(private FamilyJoinService $familyJoinService) {}

    public function __invoke(Request $request, FamilyTree $familyTree, User $inviter, string $relationshipType): RedirectResponse
    {
        $normalizedRelationshipType = strtolower($relationshipType);
        $allowedRelationshipTypes = ['father', 'mother', 'parent', 'child', 'sibling', 'aunt', 'uncle', 'cousin', 'wife', 'husband'];

        if (! in_array($normalizedRelationshipType, $allowedRelationshipTypes, true)) {
            abort(404);
        }

        $this->familyJoinService->processInviteLink($request, $familyTree, $inviter, $normalizedRelationshipType);

        return redirect()->route('register');
    }
}
