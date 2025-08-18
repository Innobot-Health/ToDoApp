<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);        // View tasks
    Route::get('/tasks/{id}', [TaskController::class, 'show']);     // View single task
    Route::post('/tasks', [TaskController::class, 'store']);       // Create task
    Route::put('/tasks/{task}', [TaskController::class, 'update']); // Update task
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // Admin-only delete
});

Route::post('/register', [RegisteredUserController::class, 'store']);

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function () {
    return auth()->user();
});

// Route::post('/password/update', [PasswordUpdateController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user', function () {
        return auth()->user();
    });
});