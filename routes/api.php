<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
//    Route::post('/auth', 'auth');
});


Route::group(['middleware' => ['auth:api']], function () {

    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
    });

    Route::controller(RegionController::class)->prefix('region')->group(function () {
        Route::get('/{id?}', 'index');
    });

    Route::controller(DistrictController::class)->prefix('district')->group(function () {
        Route::get('/', 'list');
        Route::get('/{id}', 'getDistrict');
    });



});



