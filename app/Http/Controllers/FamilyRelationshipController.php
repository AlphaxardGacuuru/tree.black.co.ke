<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFamilyRelationshipRequest;
use App\Http\Requests\StoreFamilyShareLinkRequest;
use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Services\FamilyRelationshipTypeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class FamilyRelationshipController extends Controller
{
    public function __construct(private FamilyRelationshipTypeResolver $relationshipTypeResolver) {}

    public function shareLink(StoreFamilyShareLinkRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $tree = FamilyTree::query()->findOrFail($validated['family_tree_id']);

        if (! $tree->members()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'You are not a member of this family tree.'], 403);
        }

        $relationshipType = strtolower($validated['relationship_type']);
        $shareUrl = URL::temporarySignedRoute('family-join.register', now()->addDays(7), [
            'familyTree' => $tree->id,
            'inviter' => $user->id,
            'relationshipType' => $relationshipType,
        ]);

        return response()->json([
            'message' => 'Share link created successfully.',
            'data' => [
                'share_url' => $shareUrl,
                'share_title' => $tree->name,
                'share_text' => $user->name . ' invited you to join their family tree as ' . $relationshipType . '.',
            ],
        ], 201);
    }

    public function store(StoreFamilyRelationshipRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->id === $validated['related_user_id']) {
            return response()->json([
                'message' => 'You cannot create a relationship to yourself.',
            ], 422);
        }

        $tree = FamilyTree::query()->findOrFail($validated['family_tree_id']);

        $isMember = $tree->members()->whereKey($user->id)->exists();
        if (! $isMember) {
            return response()->json(['message' => 'You are not a member of this family tree.'], 403);
        }

        $isRelatedUserMember = $tree->members()->whereKey($validated['related_user_id'])->exists();
        if (! $isRelatedUserMember) {
            return response()->json(['message' => 'The related user is not a member of this family tree.'], 422);
        }

        $relationship = FamilyRelationship::query()->firstOrCreate([
            'family_tree_id' => $tree->id,
            'user_id' => $user->id,
            'related_user_id' => $validated['related_user_id'],
            'relationship_type' => strtolower($validated['relationship_type']),
        ]);

        FamilyRelationship::query()->firstOrCreate([
            'family_tree_id' => $tree->id,
            'user_id' => $validated['related_user_id'],
            'related_user_id' => $user->id,
            'relationship_type' => $this->relationshipTypeResolver->reverse(
                strtolower($validated['relationship_type']),
                $user->gender,
            ),
        ]);

        return response()->json([
            'message' => 'Relationship created successfully.',
            'data' => $relationship,
        ], 201);
    }
}
