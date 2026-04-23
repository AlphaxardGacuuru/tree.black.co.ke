<?php

namespace App\Services;

class FamilyRelationshipTypeResolver
{
    public function reverse(string $relationshipType, ?string $sourceGender = null): string
    {
        $normalizedRelationshipType = strtolower($relationshipType);
        $normalizedGender = strtolower((string) $sourceGender);
        $isMale = $normalizedGender === 'male';
        $isFemale = $normalizedGender === 'female';

        return match ($normalizedRelationshipType) {
            'father', 'mother', 'parent' => 'child',
            'child', 'son', 'daughter' => 'parent',
            'aunt', 'uncle', 'aunt-uncle' => $isMale ? 'nephew' : ($isFemale ? 'niece' : 'niece-nephew'),
            'niece', 'nephew', 'niece-nephew' => $isMale ? 'uncle' : ($isFemale ? 'aunt' : 'aunt-uncle'),
            'sibling' => 'sibling',
            'cousin' => 'cousin',
            default => $normalizedRelationshipType,
        };
    }
}
