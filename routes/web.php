<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\VotingController;
use Illuminate\Support\Facades\Route;

// Public Voting Routes
Route::get('/', [VotingController::class, 'index'])->name('voting.index');
Route::get('/vote/{project:slug}', [VotingController::class, 'show'])->name('voting.show');
Route::post('/vote/{project:slug}/submit', [VotingController::class, 'submitFeature'])->name('voting.submit');
Route::post('/vote/feature/{feature}/vote', [VotingController::class, 'vote'])->name('voting.vote');
Route::delete('/vote/feature/{feature}/vote', [VotingController::class, 'unvote'])->name('voting.unvote');

// Admin Login
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Admin Routes (protected by middleware)
Route::middleware('admin.api')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    // Projects
    Route::get('/projects/create', [AdminController::class, 'createProject'])->name('projects.create');
    Route::post('/projects', [AdminController::class, 'storeProject'])->name('projects.store');
    Route::get('/projects/{project}/edit', [AdminController::class, 'editProject'])->name('projects.edit');
    Route::patch('/projects/{project}', [AdminController::class, 'updateProject'])->name('projects.update');
    
    // GitHub Integration
    Route::post('/projects/{project}/sync', [AdminController::class, 'syncGithub'])->name('projects.sync');
    Route::post('/github/test', [AdminController::class, 'testGithub'])->name('github.test');
    
    // Features
    Route::get('/projects/{project}/features', [AdminController::class, 'features'])->name('features');
    Route::patch('/features/{feature}', [AdminController::class, 'updateFeature'])->name('features.update');
    Route::delete('/features/{feature}', [AdminController::class, 'deleteFeature'])->name('features.delete');
});
