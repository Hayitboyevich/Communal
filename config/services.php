<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'oneId' => [
        'id' => env('ONE_ID_CLIENT_ID'),
        'secret' => env('ONE_ID_CLIENT_SECRET'),
        'redirect' => env('ONE_ID_URL'),
    ],

    "passport" => [
        "url" => 'https://api.shaffofqurilish.uz/api/v1/get-egov-token',
        "login" => env('BANK_USERNAME', 'dev@gasn'),
        "password" => env('BANK_PASSWORD', 'EkN`9?@{3v0j'),
    ],

];
