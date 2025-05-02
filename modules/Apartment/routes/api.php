<?php

use Illuminate\Support\Facades\Route;
use Modules\Apartment\Http\Controllers\MonitoringController;

Route::group(['middleware' => ['auth:api', 'check-role']], function () {

    Route::controller(MonitoringController::class)->prefix('monitoring')->group(function () {
        Route::get('/{id?}', 'index');
        Route::post('/create', 'create');
        Route::post('/create/second/{id}', 'createSecond');
    });

});
