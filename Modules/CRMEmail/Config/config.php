<?php

return [
    'name' => 'CRMEmail',

    /*
    |--------------------------------------------------------------------------
    | Email Throttling
    |--------------------------------------------------------------------------
    | Maximum number of campaign emails to dispatch per minute.
    | Each batch of jobs beyond this limit will receive an increasing delay offset.
    | Default: 60 emails per minute.
    */
    'throttle_emails_per_minute' => env('CRM_EMAIL_THROTTLE_PER_MINUTE', 60),
];

