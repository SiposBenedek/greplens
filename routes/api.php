<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RuleExportController;
use App\Http\Controllers\FindingApiController;
use App\Http\Middleware\AuthenticateApiKey;

Route::middleware([AuthenticateApiKey::class, 'throttle:60,1'])->group(function () {
    Route::get('/rules', [RuleExportController::class, 'index']);
    Route::post('/findings', [FindingApiController::class, 'store']);
});
