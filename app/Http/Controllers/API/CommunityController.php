<?php

namespace App\Http\Controllers\API;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CommunityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunityRequest;
use App\Http\Requests\UpdateCommunityRequest;

class CommunityController extends Controller
{
    public function __construct(private CommunityService $service)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->paginate()
        );
    }

    public function store(StoreCommunityRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->store($request->validated()),
            201
        );
    }

    public function show(Community $community): JsonResponse
    {
        return response()->json($community);
    }

    public function update(UpdateCommunityRequest $request, Community $community): JsonResponse
    {
        return response()->json(
            $this->service->update($community, $request->validated())
        );
    }

    public function destroy(Community $community): JsonResponse
    {
        $this->service->delete($community);

        return response()->json([
            'message' => 'Community deleted successfully.'
        ]);
    }
}