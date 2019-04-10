<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Recipients
    |--------------------------------------------------------------------------
    |
    | This value is a comma separated list of all emails that can be in the
    | _recipient field of a form hitting this apps send route.
    |
    */
    'allowed_recipients' => env('RECIPIENT_ALLOW_LIST', ''),

    /*
    |--------------------------------------------------------------------------
    | Google ReCaptcha V2 Details
    |--------------------------------------------------------------------------
    |
    | This value is for integration of Google ReCaptcha V2. If these values
    | are blank then the ReCaptcha page will not show when a submission is
    | made.
    |
    */
    'recaptcha' => [
        'enabled' => env('GOOGLE_RECAPTCHA_ENABLED', false),
        'site_key' => env('GOOGLE_RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY', ''),
    ]
];
