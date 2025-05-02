<?php

use Illuminate\Support\Facades\Route;
use Modules\Apartment\Http\Controllers\MonitoringController;
use Modules\Apartment\Http\Controllers\MonitoringStatusController;
use Modules\Apartment\Http\Controllers\InformationController;

Route::group(['middleware' => ['auth:api', 'check-role']], function () {

    Route::controller(MonitoringController::class)->prefix('monitoring')->group(function () {
        Route::get('/{id?}', 'index');
        Route::post('/create', 'create');
        Route::post('/create/second/{id}', 'createSecond');
    });

    Route::controller(MonitoringStatusController::class)->prefix('monitoring')->group(function () {
        Route::get('/status/{id?}', 'index');
    });

    Route::controller(InformationController::class)->prefix('info')->group(function () {
        Route::get('/place/{id?}', 'place');
        Route::get('/violation-type/{id?}', 'violationType');
        Route::get('/monitoring-type/{id?}', 'monitoringType');
        Route::get('/monitoring-base/{id?}', 'monitoringBase');
        Route::get('/company/{id?}', 'company');
        Route::get('/apartment/{id?}', 'apartment');

    });





});
