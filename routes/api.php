<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\JobInsightController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routes
Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);
Route::post('update-password', [AuthController::class, 'updatePassword']);
Route::get('users', [AuthController::class, 'getAllUsers']);

// Recommendation route - IMPORTANT: This should match your Android API call
Route::post('recommendations', [AuthController::class, 'getRecommendations']);

// Profile routes
Route::post('save-profile', [ProfileController::class, 'saveProfile']);

// Job insights routes
Route::get('job-insights', [JobInsightController::class, 'getInsights']);

// Test route to verify API is working
Route::get('test', function() {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});
