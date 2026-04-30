<?php

namespace App\Http\Services;

use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Models\Invitation;
use App\Models\User;
use App\Services\FamilyRelationshipTypeResolver;
use Illuminate\Http\Request;

class FamilyRelationshipService
{
    public function __construct(private FamilyRelationshipTypeResolver $typeResolver) {}

    public function store(Request $request): array
    {
        $tree = FamilyTree::findOrFail($request->familyTreeId);

        if (! $tree->members()->whereKey($request->user()->id)->exists()) {
            abort(403, 'You are not a member of this family tree.');
        }

        $sourceMember = $tree->members()->whereKey($request->userId)->first();

        if (! $sourceMember) {
            abort(422, 'The selected family member is not part of this family tree.');
        }

        if (! $tree->members()->whereKey($request->relatedUserId)->exists()) {
            abort(422, 'The related user is not a member of this family tree.');
        }

        $relationship = new FamilyRelationship;
        $relationship->family_tree_id = $tree->id;
        $relationship->user_id = $request->userId;
        $relationship->related_user_id = $request->relatedUserId;
        $relationship->relationship_type = $request->relationshipType;
        $saved = $relationship->save();

        $secondaryRelationship = new FamilyRelationship;
        $secondaryRelationship->family_tree_id = $tree->id;
        $secondaryRelationship->user_id = $request->relatedUserId;
        $secondaryRelationship->related_user_id = $request->userId;
        $secondaryRelationship->relationship_type = $this->typeResolver->reverse(
            $request->relationshipType,
            $sourceMember->gender
        );
        $saved = $secondaryRelationship->save();

        return [$saved, 'Family Relationship Created Successfully.', $relationship];
    }

    public function destroy(string $id): array
    {
        $relationship = FamilyRelationship::with('familyTree')->findOrFail($id);

        if (
            $relationship->user_id !== auth()->id() &&
            $relationship->related_user_id !== auth()->id()
        ) {
            return [false, 'You are not part of this relationship.', $relationship];
        }

        $deleted = $relationship->delete();

        return [$deleted, 'Family Relationship Removed Successfully.', $relationship];
    }

    public function generateShareLink(string $familyTreeId, string $relationshipType): array
    {
        $tree = FamilyTree::findOrFail($familyTreeId);

        if (! $tree->members()->whereKey(auth()->user()->id)->exists()) {
            abort(403, 'You are not a member of this family tree.');
        }

        $invitation = Invitation::query()->create([
            'token' => Invitation::generateToken(),
            'family_tree_id' => $tree->id,
            'inviter_id' => auth()->user()->id,
            'relationship_type' => $relationshipType,
            'expires_at' => now()->addDays(7),
        ]);

        $shareUrl = route('family-join.redeem', ['token' => $invitation->token]);

        return [
            'share_url' => $shareUrl,
            'share_title' => $tree->name,
            'share_text' => auth()->user()->name . ' invited you to join their family tree as ' . $relationshipType . '.',
        ];
    }
}
