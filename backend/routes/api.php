<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SongController;
use App\Http\Controllers\Api\SuggestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/songs', [SongController::class, 'index']);
    Route::get('/songs/top-five', [SongController::class, 'topFive']);
    Route::get('/songs/{id}', [SongController::class, 'show']);
    Route::post('/songs/{id}/play', [SongController::class, 'play']);
    Route::post('/suggestions', [SuggestionController::class, 'store']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        

        Route::post('/songs', [SongController::class, 'store']);
        Route::put('/songs/{id}', [SongController::class, 'update']);
        Route::patch('/songs/{id}', [SongController::class, 'update']);
        Route::delete('/songs/{id}', [SongController::class, 'destroy']);
        Route::put('/songs/positions', [SongController::class, 'updatePositions']);

        Route::get('/suggestions', [SuggestionController::class, 'index']);
        Route::get('/suggestions/pending', [SuggestionController::class, 'pending']);
        Route::get('/suggestions/stats', [SuggestionController::class, 'stats']);
        Route::get('/suggestions/{id}', [SuggestionController::class, 'show']);
        Route::put('/suggestions/{id}', [SuggestionController::class, 'update']);
        Route::patch('/suggestions/{id}', [SuggestionController::class, 'update']);
    });
});