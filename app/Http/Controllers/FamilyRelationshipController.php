<?php

namespace App\Http\Controllers;

use App\Http\Resources\FamilyRelationshipResource;
use App\Http\Services\FamilyRelationshipService;
use App\Models\FamilyRelationship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyRelationshipController extends Controller
{
    public function __construct(private FamilyRelationshipService $service) {}

    public function index(): JsonResponse
    {
        $tree = $this->service->index();

        return response()->json([
            'status' => true,
            'message' => 'Family Tree Loaded Successfully.',
            'data' => $tree,
        ]);
    }

    public function show(FamilyRelationship $familyRelationship)
    {
        //
    }

    public function store(Request $request): FamilyRelationshipResource|JsonResponse
    {
        $this->validate($request, [
            'userId' => ['required', 'uuid', 'exists:users,id'],
            'relatedUserId' => ['required', 'uuid', 'exists:users,id', 'different:userId'],
            'relationshipType' => ['required', 'string', 'max:50'],
        ]);

        [$code, $status, $message, $relationship] = $this->service->store($request);

        if ($code !== 200) {
            return response()->json([
                'status' => $status,
                'message' => $message,
            ], $code);
        }

        return (new FamilyRelationshipResource($relationship))
            ->additional([
                'status' => $status,
                'message' => $message,
            ])->response()
            ->setStatusCode($code);
    }

    public function update(Request $request, FamilyRelationship $familyRelationship)
    {
        //
    }

    public function destroy(string $id): FamilyRelationshipResource
    {
        [$status, $message, $relationship] = $this->service->destroy($id);

        return (new FamilyRelationshipResource($relationship))
            ->additional([
                'status' => $status,
                'message' => $message,
            ]);
    }

    public function shareLink(Request $request): JsonResponse
    {
        $this->validate($request, [
            'relationshipType' => ['required', 'string'],
        ]);

        $data = $this->service->generateShareLink(
            $request->input('relationshipType')
        );

        return response()->json([
            'message' => 'Share link created successfully.',
            'data' => $data,
        ], 201);
    }
}
