<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{

    public function __construct(private UserService $service)
    {
        $this->authorizeResource(User::class, 'user',
        [
            'except' => ['all'],
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        try {
            $users = $this->service->all(
                $request->only([
                    'role',
                    'college_id',
                ])
            );

            return response()->json([
                'message' => 'Users retrieved successfully.',
                'data' => $users,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->paginate($request->user())
        );
    }

    public function show(User $user): JsonResponse
    {
        // return response()->json($user->load([
        //     'role:id,name',
        //     'college:id,name',
        // ]));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->store($request->validated()),
            201
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        return response()->json(
            $this->service->update($user, $request->validated())
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user);
        return response()->json(['message'=>'Deleted']);
    }
}
