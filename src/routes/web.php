<?php

use Illuminate\Support\Facades\Route;
use KgySathsara\Monitor\Controllers\KgySathsaraDashboardController;

Route::group([
    'prefix' => 'kgy-sathsara',
    'middleware' => ['web', 'kgy-sathsara-auth'],
    'as' => 'kgy-sathsara.'
], function () {
    Route::get('/', [KgySathsaraDashboardController::class, 'index'])->name('dashboard');
    Route::get('/run-check', [KgySathsaraDashboardController::class, 'runCheck'])->name('dashboard.run');
    Route::get('/clear-alerts', [KgySathsaraDashboardController::class, 'clearAlerts'])->name('dashboard.clear');
});