<?php
namespace App\Http\Middleware;

use App\Services\AuditTrailService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordAuditTrail
{
    public function __construct(private readonly AuditTrailService $auditTrail) {}

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID');
        if (!is_string($requestId) || !preg_match('/^[A-Za-z0-9._-]{1,64}$/', $requestId)) $requestId = (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $response->getStatusCode() < 400) {
            try {
                $segment = (string) ($request->segment(3) ?: 'system');
                $this->auditTrail->record($request->user(), strtolower($request->method()), str_replace('-', '_', $segment), sprintf('%s %s', $request->method(), $request->path()), null, null, $request->except(['file', 'documents']), $request);
            } catch (Throwable $exception) { report($exception); }
        }
        return $response;
    }
}
