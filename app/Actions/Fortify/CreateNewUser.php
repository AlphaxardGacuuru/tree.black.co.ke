<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\FamilyRelationship;
use App\Models\Invitation;
use App\Models\User;
use App\Services\FamilyRelationshipTypeResolver;
use Illuminate\Support\Facades\DB;
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

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $familyJoinContext = request()->session()->pull('family_join_context');

            if (is_array($familyJoinContext)) {
                $inviterId = $familyJoinContext['inviter_id'] ?? null;
                $relationshipType = isset($familyJoinContext['relationship_type'])
                    ? strtolower((string) $familyJoinContext['relationship_type'])
                    : null;
                $invitationToken = $familyJoinContext['invitation_token'] ?? null;

                if (is_string($inviterId) && is_string($relationshipType)) {
                    $inviter = User::query()->find($inviterId);

                    if ($inviter) {
                        FamilyRelationship::query()->firstOrCreate([
                            'user_id' => $inviter->id,
                            'related_user_id' => $user->id,
                            'relationship_type' => $relationshipType,
                        ]);

                        $reverseType = $this->relationshipTypeResolver->reverse(
                            $relationshipType,
                            $inviter->gender,
                        );

                        FamilyRelationship::query()->firstOrCreate([
                            'user_id' => $user->id,
                            'related_user_id' => $inviter->id,
                            'relationship_type' => $reverseType,
                        ]);

                        if (is_string($invitationToken)) {
                            Invitation::query()
                                ->where('token', $invitationToken)
                                ->update([
                                    'used_by_id' => $user->id,
                                    'used_at' => now(),
                                ]);
                        }
                    }
                }
            }

            return $user;
        });
    }
}
