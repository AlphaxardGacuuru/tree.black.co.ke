<?php

namespace App\Http\Services;

use App\Http\Resources\FamilyRelationshipResource;
use App\Http\Resources\UserResource;
use App\Models\FamilyRelationship;
use App\Models\Invitation;
use App\Models\User;
use App\Services\FamilyRelationshipTypeResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FamilyRelationshipService
{
    public function __construct(private FamilyRelationshipTypeResolver $typeResolver) {}

    public function index(): array
    {
        $father = FamilyRelationship::query()->where('user_id', auth()->id())
            ->where('relationship_type', 'father')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $mother = FamilyRelationship::query()->where('user_id', auth()->id())
            ->where('relationship_type', 'mother')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $brothers = FamilyRelationship::query()->where('user_id', auth()->id())
            ->where('relationship_type', 'brother')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $sisters = FamilyRelationship::query()->where('user_id', auth()->id())
            ->where('relationship_type', 'sister')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $spouse = FamilyRelationship::query()->where('user_id', auth()->id())
            ->whereIn('relationship_type', ['husband', 'wife'])
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $sons = FamilyRelationship::query()->where('user_id', auth()->id())
            ->where('relationship_type', 'son')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $daughters = FamilyRelationship::query()->where('user_id', auth()->id())
            ->where('relationship_type', 'daughter')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        [$paternalUncles, $paternalAunts] = $this->getPaternalUnclesAndAunts($father->first());
        [$maternalUncles, $maternalAunts] = $this->getMaternalUnclesAndAunts($mother->first());

        $paternalCousins = $this->getPaternalCousins($paternalUncles, $paternalAunts);
        $maternalCousins = $this->getMaternalCousins($maternalUncles, $maternalAunts);

        $paternalNephewsAndNieces = $this->getPaternalNephewsAndNieces($brothers);
        $maternalNephewsAndNieces = $this->getMaternalNephewsAndNieces($sisters);

        $relatedUserIds = collect()
            ->merge($father->pluck('relatedUserId'))
            ->merge($mother->pluck('relatedUserId'))
            ->merge($brothers->pluck('relatedUserId'))
            ->merge($sisters->pluck('relatedUserId'))
            ->merge($spouse->pluck('relatedUserId'))
            ->merge($sons->pluck('relatedUserId'))
            ->merge($daughters->pluck('relatedUserId'))
            ->merge($paternalUncles->pluck('relatedUserId'))
            ->merge($paternalAunts->pluck('relatedUserId'))
            ->merge($maternalUncles->pluck('relatedUserId'))
            ->merge($maternalAunts->pluck('relatedUserId'))
            ->merge($paternalCousins->pluck('relatedUserId'))
            ->merge($maternalCousins->pluck('relatedUserId'))
            ->merge($paternalNephewsAndNieces->pluck('relatedUserId'))
            ->merge($maternalNephewsAndNieces->pluck('relatedUserId'))
            ->unique()
            ->values();

        $members = User::query()
            ->whereIn('id', $relatedUserIds)
            ->get();

        $relationships = FamilyRelationship::query()->where('user_id', auth()->id())
            ->whereIn('related_user_id', $members->pluck('id'))
            ->get();

        return [
            'members' => UserResource::collection($members),
            'relationships' => FamilyRelationshipResource::collection($relationships),
            'father' => $father,
            'mother' => $mother,
            'brothers' => $brothers,
            'sisters' => $sisters,
            'spouse' => $spouse,
            'sons' => $sons,
            'daughters' => $daughters,
            'paternalUncles' => $paternalUncles,
            'paternalAunts' => $paternalAunts,
            'maternalUncles' => $maternalUncles,
            'maternalAunts' => $maternalAunts,
            'paternalCousins' => $paternalCousins,
            'maternalCousins' => $maternalCousins,
            'paternalNephewsAndNieces' => $paternalNephewsAndNieces,
            'maternalNephewsAndNieces' => $maternalNephewsAndNieces,
        ];
    }

    public function store(Request $request): array
    {
        $sourceMember = User::query()->find($request->userId);

        if (! $sourceMember) {
            return [422, 'error', 'The selected family member is invalid.', null];
        }

        $existingRelationship = FamilyRelationship::query()
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->userId)
                    ->where('related_user_id', $request->relatedUserId)
                    ->where('relationship_type', $request->relationshipType);
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('user_id', $request->relatedUserId)
                    ->where('related_user_id', $request->userId)
                    ->where('relationship_type', $request->relationshipType);
            })
            ->first();

        if ($existingRelationship) {
            return [422, 'error', 'The family relationship already exists.', null];
        }

        $relationship = new FamilyRelationship;
        $relationship->user_id = $request->userId;
        $relationship->related_user_id = $request->relatedUserId;
        $relationship->relationship_type = $request->relationshipType;
        $saved = $relationship->save();

        $secondaryRelationship = new FamilyRelationship;
        $secondaryRelationship->user_id = $request->relatedUserId;
        $secondaryRelationship->related_user_id = $request->userId;
        $secondaryRelationship->relationship_type = $this->typeResolver->reverse(
            $request->relationshipType,
            $sourceMember->gender
        );
        $saved = $secondaryRelationship->save();

        return [200, $saved, 'Family Relationship Created Successfully.', $relationship];
    }

    public function destroy(string $id): array
    {
        $relationship = FamilyRelationship::findOrFail($id);

        if (
            $relationship->user_id !== auth()->id() &&
            $relationship->related_user_id !== auth()->id()
        ) {
            return [false, 'You are not part of this relationship.', $relationship];
        }

        $deleted = $relationship->delete();

        return [$deleted, 'Family Relationship Removed Successfully.', $relationship];
    }

    private function getPaternalUnclesAndAunts(?object $father): array
    {
        if (! $father) {
            return [collect([]), collect([])];
        }

        $paternalUncles = FamilyRelationship::query()->where('user_id', $father->relatedUserId)
            ->where('relationship_type', 'brother')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $paternalAunts = FamilyRelationship::query()->where('user_id', $father->relatedUserId)
            ->where('relationship_type', 'sister')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        return [$paternalUncles, $paternalAunts];
    }

    private function getMaternalUnclesAndAunts(?object $mother): array
    {
        if (! $mother) {
            return [collect([]), collect([])];
        }

        $maternalUncles = FamilyRelationship::query()->where('user_id', $mother->relatedUserId)
            ->where('relationship_type', 'brother')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        $maternalAunts = FamilyRelationship::query()->where('user_id', $mother->relatedUserId)
            ->where('relationship_type', 'sister')
            ->get()
            ->map(fn ($relationship) => $this->relationshipMap($relationship));

        return [$maternalUncles, $maternalAunts];
    }

    protected function getPaternalCousins(Collection $paternalUncles, Collection $paternalAunts): Collection
    {
        $paternalCousins = collect();

        $paternalUncles
            ->concat($paternalAunts)
            ->each(function ($uncleOrAunt) use (&$paternalCousins) {
                $cousins = FamilyRelationship::query()->where('user_id', $uncleOrAunt->relatedUserId)
                    ->whereIn('relationship_type', ['son', 'daughter'])
                    ->get()
                    ->map(fn ($relationship) => $this->relationshipMap($relationship));

                $paternalCousins = $paternalCousins->merge($cousins);
            });

        return $paternalCousins;
    }

    protected function getMaternalCousins(Collection $maternalUncles, Collection $maternalAunts): Collection
    {
        $maternalCousins = collect();

        $maternalUncles
            ->concat($maternalAunts)
            ->each(function ($uncleOrAunt) use (&$maternalCousins) {
                $cousins = FamilyRelationship::query()->where('user_id', $uncleOrAunt->relatedUserId)
                    ->whereIn('relationship_type', ['son', 'daughter'])
                    ->get()
                    ->map(fn ($relationship) => $this->relationshipMap($relationship));

                $maternalCousins = $maternalCousins->merge($cousins);
            });

        return $maternalCousins;
    }

    protected function getPaternalNephewsAndNieces(Collection $brothers): Collection
    {
        $nephewsAndNieces = collect();

        foreach ($brothers as $brother) {
            $children = FamilyRelationship::query()->where('user_id', $brother->relatedUserId)
                ->whereIn('relationship_type', ['son', 'daughter'])
                ->get()
                ->map(fn ($relationship) => $this->relationshipMap($relationship));

            $nephewsAndNieces = $nephewsAndNieces->merge($children);
        }

        return $nephewsAndNieces;
    }

    protected function getMaternalNephewsAndNieces(Collection $sisters): Collection
    {
        $nephewsAndNieces = collect();

        foreach ($sisters as $sister) {
            $children = FamilyRelationship::query()->where('user_id', $sister->relatedUserId)
                ->whereIn('relationship_type', ['son', 'daughter'])
                ->get()
                ->map(fn ($relationship) => $this->relationshipMap($relationship));

            $nephewsAndNieces = $nephewsAndNieces->merge($children);
        }

        return $nephewsAndNieces;
    }

    protected function relationshipMap(FamilyRelationship $relationship): object
    {
        return (object) [
            'id' => $relationship->id,
            'userId' => $relationship->user_id,
            'relatedUserId' => $relationship->related_user_id,
            'relationshipType' => $relationship->relationship_type,
            'name' => $relationship->relatedUser->name,
            'avatar' => $relationship->relatedUser->avatar,
        ];
    }

    public function generateShareLink(string $relationshipType): array
    {
        $inviter = auth()->user();

        if (! $inviter) {
            abort(401, 'You must be logged in to generate a share link.');
        }

        $invitation = Invitation::query()->create([
            'token' => Invitation::generateToken(),
            'inviter_id' => $inviter->id,
            'relationship_type' => $relationshipType,
            'expires_at' => now()->addDays(7),
        ]);

        $shareUrl = route('family-join.redeem', ['token' => $invitation->token]);

        return [
            'share_url' => $shareUrl,
            'share_title' => $inviter->name.' invited you to connect',
            'share_text' => $inviter->name.' invited you to join as '.$relationshipType.'.',
        ];
    }
}
