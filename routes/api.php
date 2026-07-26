<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CollegeController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\API\OutreachProgramController;
use App\Http\Controllers\API\OutreachProjectController;
use App\Http\Controllers\Api\PriorityNeedController;
use App\Http\Controllers\Api\SurveyQuestionController;
use App\Http\Controllers\Api\SurveyResponseController;
use App\Http\Controllers\Api\SurveyTemplateController;



Route::prefix('v1')->group(function () {
    Route::fallback(function () {
        return response()->json([
            'message' => 'Endpoint not found.'
        ], 404);
    });
    /*
    |--------------------------------------------------------------------------
    | AUTH ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    /*
    |--------------------------------------------------------------------------
    | PROTECTED ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        /*
        |--------------------------------------------------------------------------
        | ROLE MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/roles', [RoleController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | USER MANAGEMENT
        |--------------------------------------------------------------------------
        */
        Route::get('/users/all', [UserController::class, 'all']);
        Route::apiResource('users', UserController::class);

        /*
        |--------------------------------------------------------------------------
        | COLLEGE MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/colleges/all', [CollegeController::class, 'all']);
        Route::apiResource('colleges', CollegeController::class);
        // Route::get(
        //     'communities/all',
        //     [CommunityController::class, 'all']
        // );
        Route::apiResource('communities', CommunityController::class);
        Route::apiResource('outreach-programs', OutreachProgramController::class);

        // Route::get(
        //     '/outreach-programs/all',
        //     [OutreachProgramController::class, 'all']
        // );
        Route::apiResource('outreach-projects', OutreachProjectController::class);
        Route::apiResource(
            'survey-templates',
            SurveyTemplateController::class
        );
        Route::apiResource(
            'survey-questions',
            SurveyQuestionController::class
        );
        Route::apiResource(
            'survey-responses',
            SurveyResponseController::class
        );
        Route::get(
            'priority-needs/summary',
            [PriorityNeedController::class, 'summary']
        );
        Route::get(
            'priority-needs',
            [PriorityNeedController::class, 'index']
        );
    });

});
