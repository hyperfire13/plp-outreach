<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CollegeController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\CommunityController;

Route::prefix('v1')->group(function () {

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

        Route::apiResource('colleges', CollegeController::class);
        Route::apiResource('communities', CommunityController::class);

    });

});