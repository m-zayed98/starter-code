<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ad Creation Without Package
    |--------------------------------------------------------------------------
    |
    | When set to true, users can create ads even if they have no active
    | subscription. Set to false to require an active subscription before
    | allowing ad creation.
    |
    | Note: if the free_period is enabled in GeneralSetting, this validation
    | is skipped regardless of this value.
    |
    */
    'ENABLE_AD_CREATION_WITHOUT_PACKAGE' => env('ENABLE_AD_CREATION_WITHOUT_PACKAGE', false),

    /*
    |--------------------------------------------------------------------------
    | Allow Infinite Ads
    |--------------------------------------------------------------------------
    |
    | When set to true, users can create more ads than their subscription
    | quota allows. Set to false to enforce the package ads_count limit.
    |
    */
    'ALLOW_ADDING_INFINITE_ADS' => env('ALLOW_ADDING_INFINITE_ADS', false),

];
