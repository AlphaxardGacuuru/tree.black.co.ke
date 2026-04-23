<?php

namespace App\Http\Controllers;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FamilyJoinController extends Controller
{
	public function __invoke(Request $request, FamilyTree $familyTree, User $inviter, string $relationshipType): RedirectResponse
	{
		$normalizedRelationshipType = strtolower($relationshipType);
		$allowedRelationshipTypes = ['father', 'mother', 'parent', 'child', 'sibling', 'aunt', 'uncle', 'cousin'];

		if (! in_array($normalizedRelationshipType, $allowedRelationshipTypes, true)) {
			abort(404);
		}

		if (! $familyTree->members()->whereKey($inviter->id)->exists()) {
			abort(404);
		}

		$request->session()->put('family_join_context', [
			'family_tree_id' => $familyTree->id,
			'inviter_id' => $inviter->id,
			'relationship_type' => $normalizedRelationshipType,
			'tree_name' => $familyTree->name,
			'inviter_name' => $inviter->name,
		]);

		return redirect()->route('register');
	}
}
