<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyRelationshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'relatedUserId' => $this->related_user_id,
            'relationshipType' => $this->relationship_type,
            'name' => $this->relatedUser->name,
            'avatar' => $this->relatedUser->avatar,
        ];
    }
}
