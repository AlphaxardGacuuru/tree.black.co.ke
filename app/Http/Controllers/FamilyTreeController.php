<?php

namespace App\Http\Controllers;

use App\Http\Resources\FamilyTreeResource;
use App\Http\Services\FamilyTreeService;
use App\Models\FamilyTree;
use Illuminate\Http\Request;

class FamilyTreeController extends Controller
{
    public function __construct(private FamilyTreeService $service) {}

    public function index(Request $request)
    {
        //
    }

    public function show(string $id): FamilyTreeResource
    {
        $tree = $this->service->show($id);

        return (new FamilyTreeResource($tree))->additional([
            'status' => true,
            'message' => 'Family Tree Loaded Successfully.',
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, FamilyTree $familyTree)
    {
        //
    }

    public function destroy(FamilyTree $familyTree)
    {
        //
    }
}
