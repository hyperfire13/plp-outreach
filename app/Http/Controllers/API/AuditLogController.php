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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'module' => ['nullable', 'string', 'max:80'],
            'action' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $logs = AuditLog::query()
            ->with('actor:id,first_name,middle_name,last_name,email')
            ->when(
                $filters['module'] ?? null,
                fn ($query, $module) => $query->where('module', $module)
            )
            ->when(
                $filters['action'] ?? null,
                fn ($query, $action) => $query->where('action', $action)
            )
            ->when(
                $filters['date_from'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search): void {
                    $query->where(function ($nestedQuery) use ($search): void {
                        $nestedQuery
                            ->where('description', 'like', "%{$search}%")
                            ->orWhere('request_id', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%")
                            ->orWhereHas(
                                'actor',
                                fn ($actorQuery) => $actorQuery
                                    ->where('email', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'message' => 'Audit logs retrieved successfully.',
            'data' => $logs,
            'meta' => [
                'modules' => AuditLog::query()
                    ->distinct()
                    ->orderBy('module')
                    ->pluck('module'),
                'actions' => AuditLog::query()
                    ->distinct()
                    ->orderBy('action')
                    ->pluck('action'),
            ],
        ]);
    }
}
