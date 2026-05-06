<?php

use Illuminate\Support\Facades\Route;
use Modules\DailyReports\Http\Controllers\DailyReportController;
use Modules\DailyReports\Http\Controllers\DailyReportReportController;

Route::group(['middleware' => ['auth', 'web'], 'prefix' => 'account'], function () {

    // Employee routes
    Route::resource('daily-reports', DailyReportController::class);

    // Admin analysis view
    Route::get('reports/daily-reports', [DailyReportReportController::class, 'index'])
        ->name('reports.daily-reports');

    Route::get('reports/daily-reports/employee-wise', [DailyReportReportController::class, 'employeeWise'])
        ->name('reports.daily-reports.employee-wise');

    // Admin: missing reports tracker (date-wise)
    Route::get('reports/daily-reports/missing', [DailyReportController::class, 'missingReports'])
        ->name('daily-reports.missing');

    Route::post('daily-reports/file-store', [DailyReportController::class, 'storeFile'])
        ->name('daily-reports.store_file');

    Route::get('daily-reports/file-download/{id}', [DailyReportController::class, 'downloadFile'])
        ->name('daily-reports.download_file');

});
