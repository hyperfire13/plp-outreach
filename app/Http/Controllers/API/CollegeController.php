<?php

namespace App\Http\Controllers\API;

use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CollegeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollegeRequest;
use App\Http\Requests\UpdateCollegeRequest;

class CollegeController extends Controller
{
    public function __construct(private CollegeService $service)
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a paginated list of colleges.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->paginate()
        );
    }

    /**
     * Store a newly created college.
     */
    public function store(StoreCollegeRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->store($request->validated()),
            201
        );
    }

    /**
     * Display a single college.
     */
    public function show(College $college): JsonResponse
    {
        return response()->json($college);
    }

    /**
     * Update the specified college.
     */
    public function update(UpdateCollegeRequest $request, College $college): JsonResponse
    {
        return response()->json(
            $this->service->update($college, $request->validated())
        );
    }

    /**
     * Remove the specified college.
     */
    public function destroy(College $college): JsonResponse
    {
        $this->service->delete($college);

        return response()->json([
            'message' => 'College deleted successfully.'
        ]);
    }
}