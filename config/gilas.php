<?php

return [
    'restaurant' => [
        'name' => env('GILAS_RESTAURANT_NAME', 'گیلاس'),
        'slug' => env('GILAS_RESTAURANT_SLUG', 'gilas'),
        'description' => env('GILAS_RESTAURANT_DESCRIPTION'),
        'logo_path' => env('GILAS_RESTAURANT_LOGO_PATH'),
        'cover_image_path' => env('GILAS_RESTAURANT_COVER_IMAGE_PATH'),
        'phone' => env('GILAS_RESTAURANT_PHONE'),
        'email' => env('GILAS_RESTAURANT_EMAIL'),
        'address' => env('GILAS_RESTAURANT_ADDRESS'),
        'latitude' => env('GILAS_RESTAURANT_LATITUDE'),
        'longitude' => env('GILAS_RESTAURANT_LONGITUDE'),
        'timezone' => env('GILAS_RESTAURANT_TIMEZONE', 'Asia/Tehran'),
        'currency' => env('GILAS_RESTAURANT_CURRENCY', 'IRR'),
    ],

    // Set all three values in private .env only when creating the first owner.
    'owner' => [
        'name' => env('GILAS_OWNER_NAME'),
        'email' => env('GILAS_OWNER_EMAIL'),
        'password' => env('GILAS_OWNER_PASSWORD'),
    ],
];
