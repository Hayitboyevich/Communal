<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\InformationController;


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('auth', 'auth');
    Route::post('check-user', 'checkUser');

});


Route::group(['middleware' => ['auth:api', 'check-role']], function () {

    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('/', 'index');
        Route::get('/status', 'status');
        Route::post('/create', 'create');
        Route::post('/edit/{id}', 'edit');
        Route::post('/info', 'info');
        Route::get('/inspector/{id?}', 'inspector');
        Route::post('/organization', 'organization');
    });

    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'index');
    });

    Route::controller(RegionController::class)->prefix('region')->group(function () {
        Route::get('/{id?}', 'index');
    });

    Route::controller(RoleController::class)->prefix('role')->group(function () {
        Route::get('/{id?}', 'roles');
    });


    Route::controller(DistrictController::class)->prefix('district')->group(function () {
        Route::get('/', 'list');
        Route::get('/{id}', 'getDistrict');
    });

});

Route::group(['middleware' => ['basic']], function () {
    Route::controller(InformationController::class)->prefix('info')->group(function () {
        Route::get('/types', 'types');
        Route::get('/region/{id?}', 'region');
        Route::get('/district', 'district');
        Route::get('/protocol-water', 'protocolWater');
        Route::post('/protocol-create', 'protocolCreate');
        Route::get('/protocol-history/{id}', 'protocolHistory');
        Route::get('/protocol-status', 'protocolStatus');
        Route::get('/protocol/{id?}', 'getProtocol');
    });
});



