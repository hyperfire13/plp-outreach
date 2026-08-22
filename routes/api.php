<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CollegeController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\API\EngagementProfileController;
use App\Http\Controllers\API\EngagementRecordController;
use App\Http\Controllers\API\OutreachProgramController;
use App\Http\Controllers\API\OutreachProjectController;
use App\Http\Controllers\API\PriorityNeedController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SurveyQuestionController;
use App\Http\Controllers\API\SurveyResponseController;
use App\Http\Controllers\API\SurveyTemplateController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

$roleMiddleware = static fn (string $group): string => 'role:'.implode(',', config("role_access.{$group}"));

Route::prefix('v1')->group(function () use ($roleMiddleware) {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(
        function () use ($roleMiddleware) {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::put(
                '/profile/password',
                [ProfileController::class, 'updatePassword']
            )->middleware('throttle:5,1');

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
        }
    );

    Route::fallback(function () {
        return response()->json([
            'message' => 'Endpoint not found.',
        ], 404);
    });
});
