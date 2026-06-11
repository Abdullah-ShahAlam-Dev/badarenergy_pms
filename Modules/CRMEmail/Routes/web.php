<?php

use Illuminate\Support\Facades\Route;

// Admin CRMEmail routes
Route::group(['middleware' => ['auth', 'web'], 'prefix' => 'account'], function () {
    // Templates CRUD & Actions
    Route::get('crm-email-templates/{id}/preview', 'EmailTemplateController@preview')->name('crm-email-templates.preview');
    Route::post('crm-email-templates/{id}/duplicate', 'EmailTemplateController@duplicate')->name('crm-email-templates.duplicate');
    Route::resource('crm-email-templates', 'EmailTemplateController', ['names' => 'crm-email-templates']);

    Route::post('crm-email-segments/estimate', 'SegmentController@estimate')->name('crm-email-segments.estimate');
    Route::post('crm-email-segments/{id}/duplicate', 'SegmentController@duplicate')->name('crm-email-segments.duplicate');
    Route::resource('crm-email-segments', 'SegmentController', ['names' => 'crm-email-segments']);
    Route::resource('crm-email-campaigns', 'CampaignController', ['names' => 'crm-email-campaigns']);
    
    // Campaign trigger & status
    Route::post('crm-email-campaigns/{id}/launch', 'CampaignController@launch')->name('crm-email-campaigns.launch');
    Route::post('crm-email-campaigns/{id}/pause', 'CampaignController@pause')->name('crm-email-campaigns.pause');
    Route::get('crm-email-campaigns/{id}/stats', 'CampaignController@stats')->name('crm-email-campaigns.stats');
});

// Public CRMEmail routes (Unsubscribe, etc.)
Route::group(['middleware' => ['web']], function () {
    Route::get('crm-email/unsubscribe/{email}/{campaign_id?}', 'UnsubscribeController@unsubscribeForm')->name('crm-email.unsubscribe-form');
    Route::post('crm-email/unsubscribe', 'UnsubscribeController@unsubscribe')->name('crm-email.unsubscribe');
});
