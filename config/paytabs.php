<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Merchant profile id
    |--------------------------------------------------------------------------
    |
    | Your merchant profile id , you can find the profile id on your PayTabs Merchant’s Dashboard- profile.
    |
     */

    'profile_id' => env('PAYTABS_PROFILE_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Server Key
    |--------------------------------------------------------------------------
    |
    | You can find the Server key on your PayTabs Merchant’s Dashboard - Developers - Key management.
    |
     */

    'server_key' => env('PAYTABS_SERVER_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The currency you registered in with PayTabs account
    you must pass value from this array ['AED','EGP','SAR','OMR','JOD','US']
    |
     */

    'currency' => env('CURRENCY', 'SAR'),

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    |
    | The region you registered in with PayTabs
    you must pass value from this array ['ARE','EGY','SAU','OMN','JOR','GLOBAL']
    |
     */

    'region' => env('PAYTABS_REGION', 'SAU'),

    /*
    |--------------------------------------------------------------------------
    | The API endpoint
    |--------------------------------------------------------------------------
    |
    | The transaction request API
    |
     */
    'paytabs_api' => env('PAYTABS_API', 'https://secure.paytabs.sa/'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URL
    |--------------------------------------------------------------------------
    |
    | The redirect url after payment
    |
     */
    'redirect_url' => env('PAYTABS_REDIRECT_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Lang
    |--------------------------------------------------------------------------
    |
    | The hosted payment page lang
    |
     */
    'lang' => env('PAYTABS_LANG', app()->getLocale()),
    'model' => class_exists(App\Models\User::class) ? App\Models\User::class : App\User::class,
];
