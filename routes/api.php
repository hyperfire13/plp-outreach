<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CollegeController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\EngagementProfileController;
use App\Http\Controllers\API\EngagementRecordController;
use App\Http\Controllers\API\OutreachProgramController;
use App\Http\Controllers\API\OutreachProjectController;
use App\Http\Controllers\API\OutreachRecordController;
use App\Http\Controllers\API\PriorityNeedController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SurveyQuestionController;
use App\Http\Controllers\API\SurveyResponseController;
use App\Http\Controllers\API\SurveyTemplateController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuditLogController;
use App\Http\Controllers\API\ProjectProposalController;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Support\Facades\Route;

$roleMiddleware = static fn (string $group): string => 'role:'.implode(',', config("role_access.{$group}"));

Route::prefix('v1')->middleware('throttle:api')->group(function () use ($roleMiddleware) {
    Route::middleware('throttle:login')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'audit'])->group(
        function () use ($roleMiddleware) {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/dashboard', DashboardController::class);
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::put(
                '/profile/password',
                [ProfileController::class, 'updatePassword']
            )->middleware('throttle:sensitive');

            Route::middleware($roleMiddleware('user_managers'))
                ->group(function () {
                    Route::get('/roles', [RoleController::class, 'index']);
                    Route::get('/users/all', [UserController::class, 'all']);
                    Route::apiResource('users', UserController::class);
                });

            Route::get('/colleges/all', [CollegeController::class, 'all'])
                ->middleware($roleMiddleware('project_viewers'));
            Route::middleware($roleMiddleware('college_managers'))
                ->group(function () {
                    Route::apiResource('colleges', CollegeController::class);
                });

            Route::get(
                '/outreach-programs/all',
                [OutreachProgramController::class, 'all']
            )->middleware($roleMiddleware('program_lookup_users'));

            Route::middleware($roleMiddleware('program_viewers'))
                ->group(function () {
                    Route::apiResource(
                        'outreach-programs',
                        OutreachProgramController::class
                    );
                });

            Route::middleware($roleMiddleware('project_viewers'))
                ->group(function () {
                    Route::apiResource(
                        'outreach-projects',
                        OutreachProjectController::class
                    );
                });

            Route::middleware($roleMiddleware('outreach_record_viewers'))
                ->group(function () {
                    Route::get(
                        '/outreach-records/options',
                        [OutreachRecordController::class, 'options']
                    );
                    Route::apiResource(
                        'outreach-records',
                        OutreachRecordController::class
                    );
                });

            Route::get(
                '/communities/all',
                [CommunityController::class, 'all']
            )->middleware($roleMiddleware('community_lookup_users'));

            Route::middleware($roleMiddleware('community_managers'))
                ->group(function () {
                    Route::apiResource(
                        'communities',
                        CommunityController::class
                    );
                });

            Route::middleware($roleMiddleware('survey_respondents'))
                ->group(function () {
                    Route::get(
                        '/survey-templates',
                        [SurveyTemplateController::class, 'index']
                    )->name('survey-templates.index');
                    Route::get(
                        '/survey-templates/{survey_template}',
                        [SurveyTemplateController::class, 'show']
                    )->name('survey-templates.show');
                    Route::get(
                        '/survey-templates/{survey_template}/pdf',
                        [SurveyTemplateController::class, 'downloadPdf']
                    )->middleware('throttle:pdf-downloads')
                        ->name('survey-templates.pdf');
                });

            Route::middleware($roleMiddleware('survey_designers'))
                ->group(function () {
                    Route::post(
                        '/survey-templates',
                        [SurveyTemplateController::class, 'store']
                    )->name('survey-templates.store');
                    Route::match(
                        ['put', 'patch'],
                        '/survey-templates/{survey_template}',
                        [SurveyTemplateController::class, 'update']
                    )->name('survey-templates.update');
                    Route::delete(
                        '/survey-templates/{survey_template}',
                        [SurveyTemplateController::class, 'destroy']
                    )->name('survey-templates.destroy');

                    Route::apiResource(
                        'survey-questions',
                        SurveyQuestionController::class
                    );
                });

            Route::middleware($roleMiddleware('survey_respondents'))
                ->group(function () {
                    Route::apiResource(
                        'survey-responses',
                        SurveyResponseController::class
                    );
                });

            Route::middleware($roleMiddleware('priority_need_viewers'))
                ->group(function () {
                    Route::get(
                        '/priority-needs/summary',
                        [PriorityNeedController::class, 'summary']
                    );
                    Route::get(
                        '/priority-needs',
                        [PriorityNeedController::class, 'index']
                    );
                });

            Route::middleware($roleMiddleware('priority_need_reviewers'))->group(function () {
                Route::post('/priority-needs/{priority_need}/validate', [PriorityNeedController::class, 'validateNeed']);
                Route::post('/priority-needs/{priority_need}/reject', [PriorityNeedController::class, 'rejectNeed']);
            });

            Route::middleware($roleMiddleware('engagement_viewers'))
                ->group(function () {
                    Route::get(
                        '/engagement-records/options',
                        [EngagementRecordController::class, 'options']
                    );
                    Route::get(
                        '/engagement-profiles/me',
                        [EngagementProfileController::class, 'me']
                    );
                    Route::get(
                        '/engagement-profiles/{user}',
                        [EngagementProfileController::class, 'show']
                    );
                    Route::get(
                        '/engagement-profiles/me/pdf',
                        [EngagementProfileController::class, 'downloadMyPdf']
                    )->middleware('throttle:pdf-downloads')
                        ->name('engagement-profiles.me.pdf');
                    Route::get(
                        '/engagement-profiles/{user}/pdf',
                        [EngagementProfileController::class, 'downloadPdf']
                    )->middleware('throttle:pdf-downloads')
                        ->name('engagement-profiles.pdf');
                    Route::get(
                        '/engagement-records',
                        [EngagementRecordController::class, 'index']
                    )->name('engagement-records.index');
                    Route::get(
                        '/engagement-records/{engagement_record}',
                        [EngagementRecordController::class, 'show']
                    )->name('engagement-records.show');
                });

            Route::middleware($roleMiddleware('engagement_encoders'))
                ->group(function () {
                    Route::post(
                        '/engagement-records',
                        [EngagementRecordController::class, 'store']
                    )->name('engagement-records.store');
                    Route::match(
                        ['put', 'patch'],
                        '/engagement-records/{engagement_record}',
                        [EngagementRecordController::class, 'update']
                    )->name('engagement-records.update');
                    Route::delete(
                        '/engagement-records/{engagement_record}',
                        [EngagementRecordController::class, 'destroy']
                    )->name('engagement-records.destroy');
                    Route::post(
                        '/engagement-records/{engagement_record}/submit',
                        [EngagementRecordController::class, 'submit']
                    );
                });

            Route::middleware($roleMiddleware('engagement_reviewers'))
                ->group(function () {
                    Route::post(
                        '/engagement-records/{engagement_record}/approve',
                        [EngagementRecordController::class, 'approve']
                    );
                    Route::post(
                        '/engagement-records/{engagement_record}/reject',
                        [EngagementRecordController::class, 'reject']
                    );
                });

            Route::middleware($roleMiddleware('proposal_viewers'))->group(function () {
                Route::get('/project-proposals/options', [ProjectProposalController::class, 'options']);
                Route::get('/project-proposals', [ProjectProposalController::class, 'index']);
                Route::get('/project-proposals/{project_proposal}', [ProjectProposalController::class, 'show']);
                Route::get('/project-proposals/{project_proposal}/ntp/pdf', [ProjectProposalController::class, 'downloadNtp'])->middleware('throttle:pdf-downloads');
                Route::post('/project-proposals', [ProjectProposalController::class, 'store']);
                Route::match(['put', 'patch'], '/project-proposals/{project_proposal}', [ProjectProposalController::class, 'update']);
                Route::delete('/project-proposals/{project_proposal}', [ProjectProposalController::class, 'destroy']);
                Route::post('/project-proposals/{project_proposal}/submit', [ProjectProposalController::class, 'submit']);
                Route::post('/project-proposals/{project_proposal}/review', [ProjectProposalController::class, 'review']);
                Route::post('/project-proposals/{project_proposal}/issue-ntp', [ProjectProposalController::class, 'issueNtp']);
                Route::post('/project-proposals/{project_proposal}/documents', [ProjectProposalController::class, 'uploadDocument']);
                Route::get('/project-proposals/{project_proposal}/documents/{document}', [ProjectProposalController::class, 'downloadDocument']);
                Route::delete('/project-proposals/{project_proposal}/documents/{document}', [ProjectProposalController::class, 'deleteDocument']);
            });

            Route::get('/audit-logs', [AuditLogController::class, 'index'])
                ->middleware($roleMiddleware('audit_viewers'));
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
            Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        }
    );

    Route::fallback(function () {
        return response()->json([
            'message' => 'Endpoint not found.',
        ], 404);
    });
});
