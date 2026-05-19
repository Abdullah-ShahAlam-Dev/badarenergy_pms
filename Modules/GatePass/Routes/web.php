<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::resource('gate-pass', 'GatePassController', ['names' => 'gate-pass']);
    
    // Approval Routes
    Route::post('gate-pass/hod-action/{id}', 'GatePassApprovalController@hodAction')->name('gate-pass.hod-action');
    Route::post('gate-pass/store-action/{id}', 'GatePassApprovalController@storeAction')->name('gate-pass.store-action');
    Route::post('gate-pass/security-action/{id}', 'GatePassApprovalController@securityAction')->name('gate-pass.security-action');
    Route::post('gate-pass/record-return/{id}', 'GatePassApprovalController@recordReturn')->name('gate-pass.record-return');
    Route::post('gate-pass/manually-close/{id}', 'GatePassApprovalController@manuallyClose')->name('gate-pass.manually-close');
    
    // Printing & QR
    Route::get('gate-pass/print/{id}', 'GatePassController@printPass')->name('gate-pass.print');
    Route::get('gate-pass/verify/{hash}', 'GatePassController@verifyPass')->name('gate-pass.verify');
    // Reports
    Route::get('gate-pass-report', 'GatePassReportController@index')->name('gate-pass.report');
});
