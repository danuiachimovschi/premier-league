<?php

use App\Infrastructure\Http\Controllers\Api\LeagueTableController;
use App\Infrastructure\Http\Controllers\Api\MatchController;
use App\Infrastructure\Http\Controllers\Api\PredictionController;
use App\Infrastructure\Http\Controllers\Api\SeasonController;
use App\Infrastructure\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

// Teams
Route::get('/teams', [TeamController::class, 'index']);
Route::get('/teams/{team}', [TeamController::class, 'show']);

// Seasons
Route::get('/seasons', [SeasonController::class, 'index']);
Route::post('/seasons', [SeasonController::class, 'store']);
Route::get('/seasons/{season}', [SeasonController::class, 'show']);
Route::delete('/seasons/{season}/reset', [SeasonController::class, 'reset']);

// Season matches
Route::get('/seasons/{season}/matches', [MatchController::class, 'index']);
Route::post('/seasons/{season}/generate-week', [MatchController::class, 'generateWeek']);
Route::post('/seasons/{season}/simulate-all', [MatchController::class, 'simulateAll']);

// Individual matches
Route::put('/matches/{match}', [MatchController::class, 'update']);

// League table
Route::get('/seasons/{season}/table', [LeagueTableController::class, 'index']);

// Predictions
Route::get('/seasons/{season}/predictions', [PredictionController::class, 'index']);
