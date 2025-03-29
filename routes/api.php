<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('auth', 'auth');
    Route::post('check-user', 'checkUser');

});


Route::group(['middleware' => ['auth:api']], function () {

    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
    });

    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'index');
    });

    Route::controller(RegionController::class)->prefix('region')->group(function () {
        Route::get('/{id?}', 'index');
    });

    Route::controller(RoleController::class)->prefix('role')->group(function () {
        Route::get('/{id?}', 'index');
    });


    Route::controller(DistrictController::class)->prefix('district')->group(function () {
        Route::get('/', 'list');
        Route::get('/{id}', 'getDistrict');
    });

});



