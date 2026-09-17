<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255'], 'module' => ['nullable', 'string', 'max:80'], 'action' => ['nullable', 'string', 'max:80'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $logs = AuditLog::query()->with('actor:id,first_name,middle_name,last_name,email')
            ->when($filters['module'] ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($nested) => $nested->where('description', 'like', "%{$v}%")->orWhereHas('actor', fn ($actor) => $actor->where('email', 'like', "%{$v}%"))))
            ->latest('created_at')->paginate($filters['per_page'] ?? 20);

        return response()->json(['message' => 'Audit logs retrieved successfully.', 'data' => $logs]);
    }
}
