<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\ChecklistTemplateController;
use App\Http\Controllers\Api\InspectionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;

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

// Authentication routes for mobile app
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API routes for mobile app
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard stats
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Assets API
    Route::get('/assets', [AssetController::class, 'index']);
    Route::get('/assets/{asset:qr_code}', [AssetController::class, 'show']);
    Route::post('/assets/{asset:qr_code}/inspect', [AssetController::class, 'inspect']);

    // Checklist Templates API
    Route::get('/checklists', [ChecklistTemplateController::class, 'index']);
    Route::get('/checklists/{template}', [ChecklistTemplateController::class, 'show']);

    // Inspections API
    Route::get('/inspections', [InspectionController::class, 'index']);
    Route::get('/inspections/{inspection}', [InspectionController::class, 'show']);
    Route::post('/inspections', [InspectionController::class, 'store']);
    Route::post('/inspections/{inspection}/complete', [InspectionController::class, 'complete']);

    // Notifications API
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/statistics', [NotificationController::class, 'statistics']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});
