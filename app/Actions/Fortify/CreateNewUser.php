<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\FamilyRelationship;
use App\Models\FamilyTree;
use App\Models\User;
use App\Services\FamilyRelationshipTypeResolver;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private FamilyRelationshipTypeResolver $relationshipTypeResolver) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $familyJoinContext = request()->session()->pull('family_join_context');

        if (is_array($familyJoinContext)) {
            $familyTreeId = $familyJoinContext['family_tree_id'] ?? null;
            $inviterId = $familyJoinContext['inviter_id'] ?? null;
            $relationshipType = isset($familyJoinContext['relationship_type'])
                ? strtolower((string) $familyJoinContext['relationship_type'])
                : null;

            if (is_string($familyTreeId) && is_string($inviterId) && is_string($relationshipType)) {
                $tree = FamilyTree::query()->find($familyTreeId);
                $inviter = User::query()->find($inviterId);

                if ($tree && $inviter && $tree->members()->whereKey($inviter->id)->exists()) {
                    $tree->members()->syncWithoutDetaching([
                        $user->id => [
                            'role' => 'member',
                            'joined_at' => now(),
                        ],
                    ]);

                    FamilyRelationship::query()->firstOrCreate([
                        'family_tree_id' => $tree->id,
                        'user_id' => $inviter->id,
                        'related_user_id' => $user->id,
                        'relationship_type' => $relationshipType,
                    ]);

                    FamilyRelationship::query()->firstOrCreate([
                        'family_tree_id' => $tree->id,
                        'user_id' => $user->id,
                        'related_user_id' => $inviter->id,
                        'relationship_type' => $this->relationshipTypeResolver->reverse(
                            $relationshipType,
                            $inviter->gender,
                        ),
                    ]);

                    return $user;
                }
            }
        }

        $tree = FamilyTree::query()->create([
            'name' => $user->name . ' Family Tree',
            'created_by' => $user->id,
        ]);

        $tree->members()->syncWithoutDetaching([
            $user->id => [
                'role' => 'owner',
                'joined_at' => now(),
            ],
        ]);

        return $user;
    }
}
