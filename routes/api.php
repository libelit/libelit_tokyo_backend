<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\Developer\DeveloperProfileController;
use App\Http\Controllers\Api\Developer\DeveloperKybController;
use App\Http\Controllers\Api\Developer\DeveloperProjectController;
use App\Http\Controllers\Api\Developer\ProjectDocumentController;
use App\Http\Controllers\Api\Developer\ProjectMilestoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['api'])
    ->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('logout.user');
            Route::get('/user', function (Request $request) {
                return $request->user();
            });

            // Projects (public - for lenders)
            Route::get('/projects', [ProjectController::class, 'index']);

            // Developer Routes
            Route::prefix('developer')->middleware(['developer'])->group(function () {
                // Profile
                Route::get('profile', [DeveloperProfileController::class, 'show']);
                Route::put('profile', [DeveloperProfileController::class, 'update']);

                // KYB
                Route::get('kyb/documents', [DeveloperKybController::class, 'index']);
                Route::get('kyb/documents/{id}', [DeveloperKybController::class, 'show']);
                Route::post('kyb/documents', [DeveloperKybController::class, 'store']);
                Route::delete('kyb/documents/{id}', [DeveloperKybController::class, 'destroy']);
                Route::post('kyb/submit', [DeveloperKybController::class, 'submit']);

                // Projects
                Route::get('projects', [DeveloperProjectController::class, 'index']);
                Route::post('projects', [DeveloperProjectController::class, 'store']);
                Route::get('projects/{id}', [DeveloperProjectController::class, 'show']);
                Route::put('projects/{id}', [DeveloperProjectController::class, 'update']);
                Route::delete('projects/{id}', [DeveloperProjectController::class, 'destroy']);
                Route::post('projects/{id}/submit', [DeveloperProjectController::class, 'submit']);

                // Project Documents
                Route::get('projects/{projectId}/documents', [ProjectDocumentController::class, 'index']);
                Route::post('projects/{projectId}/documents', [ProjectDocumentController::class, 'store']);
                Route::delete('projects/{projectId}/documents/{id}', [ProjectDocumentController::class, 'destroy']);

                // Project Milestones
                Route::get('projects/{projectId}/milestones', [ProjectMilestoneController::class, 'index']);
                Route::post('projects/{projectId}/milestones', [ProjectMilestoneController::class, 'store']);
                Route::post('projects/{projectId}/milestones/{milestoneId}/complete', [ProjectMilestoneController::class, 'complete']);

                // Milestone Proofs
                Route::get('projects/{projectId}/milestones/{milestoneId}/proofs', [ProjectMilestoneController::class, 'listProofs']);
                Route::post('projects/{projectId}/milestones/{milestoneId}/proofs', [ProjectMilestoneController::class, 'uploadProofs']);
                Route::delete('projects/{projectId}/milestones/{milestoneId}/proofs/{proofId}', [ProjectMilestoneController::class, 'deleteProof']);
            });
        });
});

