<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(private RoleService $service)
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of roles.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            $this->service->all()
        );
    }
}