<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    // ─── Vendor Routes ────────────────────────────────────────────────────────
    Route::resource('vendors', 'VendorController', ['names' => 'vendors']);

    // ─── Approval Mapping Routes ──────────────────────────────────────────────
    Route::resource('approval-mappings', 'ApprovalMappingController', ['names' => 'approval-mappings']);

    // ─── Work Order Routes ────────────────────────────────────────────────────
    Route::resource('work-orders', 'WorkOrderController', ['names' => 'work-orders']);

    // Approval actions
    Route::post('work-orders/{id}/approve',   'WorkOrderApprovalController@approve')->name('work-orders.approve');
    Route::post('work-orders/{id}/reject',    'WorkOrderApprovalController@reject')->name('work-orders.reject');
    Route::post('work-orders/{id}/send-back', 'WorkOrderApprovalController@sendBack')->name('work-orders.send-back');

    // Duplicate
    Route::post('work-orders/{id}/duplicate', 'WorkOrderController@duplicate')->name('work-orders.duplicate');

    // PDF / Print
    Route::get('work-orders/{id}/pdf',   'WorkOrderController@downloadPdf')->name('work-orders.pdf');
    Route::get('work-orders/{id}/print', 'WorkOrderController@printView')->name('work-orders.print');

    // Vendor Payments
    Route::resource('vendor-payments', 'VendorPaymentController', ['names' => 'vendor-payments']);

    // Reports
    Route::get('work-order-reports', 'WorkOrderReportController@index')->name('work-orders.report');
    Route::post('work-order-reports/data', 'WorkOrderReportController@data')->name('work-orders.report.data');
});
