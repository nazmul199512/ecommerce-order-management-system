<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    |
    | This secret key is used to sign your tokens. You can generate one using:
    | php artisan jwt:secret
    |
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token will be valid for.
    | Defaults to 60 minutes.
    |
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Refresh time to live
    |--------------------------------------------------------------------------
    |
    | The length of time (in minutes) that the token can be refreshed within.
    | Defaults to 2 weeks.
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT Hashing Algorithm
    |--------------------------------------------------------------------------
    |
    | Specify the hashing algorithm that will be used to sign the token.
    | Defaults to HS256.
    |
    */
    'algo' => env('JWT_ALGO', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | Required Claims
    |--------------------------------------------------------------------------
    |
    | Specify the claims that must exist in any token.
    |
    */
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    /*
    |--------------------------------------------------------------------------
    | Persistent Claims
    |--------------------------------------------------------------------------
    |
    | Claims that will persist across token refreshes.
    |
    */
    'persistent_claims' => [],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Specify the classes that provide JWT, Auth, and Storage services.
    |
    */
    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Blacklist Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable token blacklisting.
    |
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Grace Period
    |--------------------------------------------------------------------------
    |
    | The grace period (in seconds) for the blacklist.
    |
    */
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | HTTP Only Cookie
    |--------------------------------------------------------------------------
    |
    | If using cookies to store the JWT, enable HTTP only cookies.
    |
    */
    'http_only' => true,

    /*
    |--------------------------------------------------------------------------
    | JWT Header
    |--------------------------------------------------------------------------
    |
    | Specify the HTTP header that will carry the token.
    |
    */
    'header' => 'Authorization',

    'header_prefix' => 'Bearer',

    /*
    |--------------------------------------------------------------------------
    | JWT Cookie Name
    |--------------------------------------------------------------------------
    |
    | If storing the JWT in a cookie, set the cookie name here.
    |
    */
    'cookie' => env('JWT_COOKIE', null),
];
