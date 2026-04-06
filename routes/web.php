<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\RuleExportController;
use App\Http\Controllers\RuleImportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/findings', [FindingController::class, 'index'])->name('findings.index');
    Route::get('/findings/{project}', [FindingController::class, 'show'])->name('findings.show');
    Route::patch('/findings/{finding}/status', [FindingController::class, 'updateStatus'])->name('findings.update-status');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project}/regenerate-key', [ProjectController::class, 'regenerateKey'])->name('projects.regenerate-key');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');


    Route::get('/rules', [RuleController::class, 'index'])->name('rules.index');
    Route::get('/rules/create', [RuleController::class, 'create'])->name('rules.create');
    Route::post('/rules', [RuleController::class, 'store'])->name('rules.store');
    Route::patch('/rules/{rule}', [RuleController::class, 'update'])->name('rules.update');
    Route::get('/rules/{rule}/editor', [RuleController::class, 'editor'])->name('rules.editor');
    Route::post('/rules/{rule}/test/run', [RuleController::class, 'runTest'])->middleware('throttle:10,1')->name('rules.run-test');
    Route::patch('/rules/{rule}/yaml', [RuleController::class, 'updateYaml'])->name('rules.update-yaml');
    Route::get('/rules/import', [RuleImportController::class, 'showForm'])->name('rules.import');
    Route::post('/rules/import', [RuleImportController::class, 'import'])->name('rules.import.process');
    Route::get('/rules/export', [RuleExportController::class, 'index'])->name('rules.export');
});
