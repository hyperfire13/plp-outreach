<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EngagementRecord;
use App\Models\User;
use App\Services\EngagementProfileService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class EngagementProfileController extends Controller
{
    public function __construct(
        private readonly EngagementProfileService $service
    ) {}

    public function me(Request $request): JsonResponse
    {
        return $this->profileResponse($request, $request->user());
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize(
            'viewProfile',
            [EngagementRecord::class, $user]
        );

        return $this->profileResponse($request, $user);
    }

    public function downloadMyPdf(Request $request): Response
    {
        return $this->pdfResponse($request, $request->user());
    }

    public function downloadPdf(Request $request, User $user): Response
    {
        $this->authorize('viewProfile', [EngagementRecord::class, $user]);

        return $this->pdfResponse($request, $user);
    }

    private function profileResponse(
        Request $request,
        User $user
    ): JsonResponse {
        $filters = $this->validatedFilters($request, true);

        return response()->json([
            'message' => 'Engagement profile retrieved successfully.',
            'data' => $this->service->get($user, $filters),
        ]);
    }

    private function pdfResponse(Request $request, User $user): Response
    {
        $filters = $this->validatedFilters($request, false);
        $profile = $this->service->getForExport($user, $filters);
        $safeName = str($user->full_name ?: 'engagement-profile')
            ->slug('-')
            ->limit(80, '');

        return Pdf::loadView('pdf.engagement-profile', $profile)
            ->setPaper('a4', 'landscape')
            ->download("{$safeName}-engagement-profile.pdf");
    }

    private function validatedFilters(Request $request, bool $includePagination): array
    {
        $rules = [
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'engagement_type' => [
                'nullable',
                Rule::in(EngagementRecord::TYPES),
            ],
            'sdg' => ['nullable', Rule::in(EngagementRecord::SDGS)],
        ];

        if ($includePagination) {
            $rules['per_page'] = ['nullable', 'integer', 'min:1', 'max:100'];
        }

        return $request->validate($rules);
    }
}
