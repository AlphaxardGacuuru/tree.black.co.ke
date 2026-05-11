<?php

namespace App\Services;

class FamilyRelationshipTypeResolver
{
    /**
     * Given a relationship type (what $related IS to $source) and the source person's gender,
     * return the inverse type (what $source IS to $related).
     *
     * @param  string  $relationshipType  What the related person IS to the source person.
     * @param  string|null  $sourceGender  Gender of the source person.
     */
    public function reverse(string $relationshipType, ?string $sourceGender = null): string
    {
        $normalizedRelationshipType = strtolower($relationshipType);
        $normalizedGender = strtolower((string) $sourceGender);
        $isMale = $normalizedGender === 'male';
        $isFemale = $normalizedGender === 'female';

        return match ($normalizedRelationshipType) {
            'father', 'mother', 'parent' => $isMale ? 'son' : ($isFemale ? 'daughter' : 'child'),
            'child', 'son', 'daughter' => $isMale ? 'father' : ($isFemale ? 'mother' : 'parent'),
            'brother', 'sister', 'sibling' => $isMale ? 'brother' : ($isFemale ? 'sister' : 'sibling'),
            'aunt', 'uncle', 'aunt-uncle' => $isMale ? 'nephew' : ($isFemale ? 'niece' : 'niece-nephew'),
            'niece', 'nephew', 'niece-nephew' => $isMale ? 'uncle' : ($isFemale ? 'aunt' : 'aunt-uncle'),
            'cousin' => 'cousin',
            'wife' => 'husband',
            'husband' => 'wife',
            default => $normalizedRelationshipType,
        };
    }

    /**
     * Derive what the OTHER person IS to the NEW USER, given:
     *  - $inviterToNewUser: what the new user IS to the inviter (from the inviter's perspective)
     *  - $inviterToOther:   what the other person IS to the inviter
     *  - $otherGender:      gender of the other person (used for gendered output types)
     *
     * Returns null when the relationship cannot be determined or is unsupported (e.g. grandparent).
     *
     * @param  string  $inviterToNewUser  e.g. "son"
     * @param  string  $inviterToOther  e.g. "wife"
     */
    public function derive(string $inviterToNewUser, string $inviterToOther, ?string $otherGender = null): ?string
    {
        $inviterToNewUser = strtolower($inviterToNewUser);
        $inviterToOther = strtolower($inviterToOther);
        $normalizedGender = strtolower((string) $otherGender);
        $isMale = $normalizedGender === 'male';
        $isFemale = $normalizedGender === 'female';

        // Gendered output types based on the OTHER person's gender.
        $parentType = $isMale ? 'father' : ($isFemale ? 'mother' : 'parent');
        $childType = $isMale ? 'son' : ($isFemale ? 'daughter' : 'child');
        $siblingType = $isMale ? 'brother' : ($isFemale ? 'sister' : 'sibling');
        $uncleType = $isMale ? 'uncle' : ($isFemale ? 'aunt' : 'aunt-uncle');
        $nephewType = $isMale ? 'nephew' : ($isFemale ? 'niece' : 'niece-nephew');

        // Inviter is a parent of new user (new user is son/daughter of inviter).
        if (in_array($inviterToNewUser, ['son', 'daughter', 'child'])) {
            return match ($inviterToOther) {
                'wife', 'husband' => $parentType,           // inviter's spouse  → new user's parent
                'son', 'daughter', 'child' => $siblingType, // inviter's child   → new user's sibling
                'brother', 'sister', 'sibling' => $uncleType, // inviter's sibling → new user's uncle/aunt
                default => null,
            };
        }

        // Inviter is a child of new user (new user is parent of inviter).
        if (in_array($inviterToNewUser, ['father', 'mother', 'parent'])) {
            return match ($inviterToOther) {
                // Inviter's siblings are also children of the new user.
                'brother', 'sister', 'sibling' => $childType,
                default => null,
            };
        }

        // Inviter is a sibling of new user.
        if (in_array($inviterToNewUser, ['brother', 'sister', 'sibling'])) {
            return match ($inviterToOther) {
                'father', 'mother', 'parent' => $parentType,     // shared parent
                'son', 'daughter', 'child' => $nephewType,       // inviter's child → new user's niece/nephew
                'brother', 'sister', 'sibling' => $siblingType,  // shared sibling
                default => null,
            };
        }

        // Inviter is the spouse of new user.
        if (in_array($inviterToNewUser, ['wife', 'husband'])) {
            return match ($inviterToOther) {
                'son', 'daughter', 'child' => $childType, // shared children
                default => null,
            };
        }

        // Inviter is an uncle/aunt of new user (new user is a nephew/niece).
        if (in_array($inviterToNewUser, ['uncle', 'aunt', 'aunt-uncle'])) {
            return match ($inviterToOther) {
                'son', 'daughter', 'child' => 'cousin',           // inviter's child → new user's cousin
                'brother', 'sister', 'sibling' => $uncleType,     // inviter's sibling → another uncle/aunt
                default => null,
            };
        }

        // Inviter is a nephew/niece of new user (new user is an uncle/aunt).
        if (in_array($inviterToNewUser, ['nephew', 'niece', 'niece-nephew'])) {
            return match ($inviterToOther) {
                'father', 'mother', 'parent' => $siblingType, // inviter's parent is new user's sibling
                'brother', 'sister', 'sibling' => $nephewType, // inviter's sibling is another niece/nephew
                default => null,
            };
        }

        return null;
    }
}
