<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CollegeController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\API\OutreachProgramController;
use App\Http\Controllers\API\OutreachProjectController;


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

        Route::apiResource('users', UserController::class);

        /*
        |--------------------------------------------------------------------------
        | COLLEGE MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/colleges/all', [CollegeController::class, 'all']);
        Route::apiResource('colleges', CollegeController::class);
        Route::apiResource('communities', CommunityController::class);
        Route::apiResource('outreach-programs', OutreachProgramController::class);

        // Route::get(
        //     '/outreach-programs/all',
        //     [OutreachProgramController::class, 'all']
        // );
        Route::apiResource('outreach-projects', OutreachProjectController::class);
    });

});
