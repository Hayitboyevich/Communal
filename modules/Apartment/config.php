
<?php

return [
    'company' => [
        'url' => 'https://back.meninguyim.mjko.uz/api/company-list-basic',
        'login' => 'R2025Estr',
        'password' => 'InfoMC&^_0@2025nY',
    ],

    'home' => [
        'url' => 'https://back.meninguyim.mjko.uz/api/api-home-list?companyid=',
        'login' => '076f02b1748d7043b622205c434c60898b1a26cce4bfd4fb80b63126eff7b394',
        'password' => '*&!&#alia'.now()->format('Y-m-d H'),
    ],
    'cadastr' => [
        'url' => 'https://api.shaffofqurilish.uz/api/v1/get-cad-info',
        'login' => 'dev@gasn',
        'password' => 'EkN`9?@{3v0j',
    ],
    'hybrid' => [
        'url' => 'https://gmtest.post.uz',
        'grant_type' => 'client_credentials',
        'client_id' => 'GASNClientId',
        'client_secret' => 'GASNClientSecret',
//        'url' => 'https://hybrid.pochta.uz',
//        'grant_type' => 'password',
//        'username' => '998998599402',
//        'password' => '11223344',
    ]
];
