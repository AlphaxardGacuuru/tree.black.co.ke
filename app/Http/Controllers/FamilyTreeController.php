<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class FamilyTreeController extends Controller
{
    public function index(): JsonResponse
    {
        $tree = auth()->user()
            ->familyTrees()
            ->select(['family_trees.id', 'family_trees.name', 'family_trees.created_by', 'family_trees.created_at'])
            ->with([
                'members:id,name,email,gender,avatar_url',
                'relationships:id,family_tree_id,user_id,related_user_id,relationship_type',
            ])
            ->first();

        return response()->json(['data' => $tree]);
    }
}
