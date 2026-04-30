<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyTreeResource extends JsonResource
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
            'name' => $this->name,
            'creatorId' => $this->creator?->id,
            'creatorName' => $this->creator?->name,
            'creatorGender' => $this->creator?->gender,
            'creatorAvatar' => $this->creator?->avatar,
            // 'members' => UserResource::collection($this->members),
            // 'relationships' => FamilyRelationshipResource::collection($this->relationships),
            'father' => $this->nodes['father'] ?? [],
            'mother' => $this->nodes['mother'] ?? [],
            'brothers' => $this->nodes['brother'] ?? [],
            'sisters' => $this->nodes['sister'] ?? [],
            'sons' => $this->nodes['son'] ?? [],
            'daughters' => $this->nodes['daughter'] ?? [],
            'paternalUncles' => $this->nodes['paternalUncles'] ?? [],
            'paternalAunts' => $this->nodes['paternalAunts'] ?? [],
            'maternalUncles' => $this->nodes['maternalUncles'] ?? [],
            'maternalAunts' => $this->nodes['maternalAunts'] ?? [],
            'nodes' => $this->nodes,
        ];
    }
}
