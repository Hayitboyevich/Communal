<?php

use Illuminate\Support\Facades\Route;
use Modules\Apartment\Http\Controllers\MonitoringController;
use Modules\Apartment\Http\Controllers\MonitoringStatusController;
use Modules\Apartment\Http\Controllers\InformationController;
use Modules\Apartment\Http\Controllers\ClaimController;
use Modules\Apartment\Http\Controllers\ProgramController;
use Modules\Apartment\Http\Controllers\ProgramMonitoringController;
use Modules\Apartment\Http\Controllers\ProgramObjectController;
use Modules\Apartment\Http\Controllers\ChecklistController;
use Modules\Apartment\Http\Controllers\LetterController;

Route::group(['middleware' => ['auth:api', 'check-role']], function () {

    Route::controller(MonitoringController::class)->prefix('monitoring')->group(function () {
        Route::post('/create', 'create');
        Route::post('/create/second/{id}', 'createSecond');
        Route::post('/create/third/{id}', 'createThird');
        Route::post('/confirm/{id}', 'confirm');
        Route::post('/reject/{id}', 'reject');
        Route::post('/change-status/{id}', 'changeStatus');
        Route::post('/confirm-regulation/{id}', 'confirmRegulation');
        Route::post('/reject-regulation/{id}', 'rejectRegulation');
        Route::get('/count', 'count');
        Route::get('/pdf/{id}', 'pdf');
        Route::get('/report/{id?}', 'report');
        Route::get('/month', 'getMonth');
        Route::get('/history/{id}', 'history');
        Route::post('/attach', 'attach');
        Route::get('/excel/{id}', 'excel');
        Route::post('/delete/{id}', 'delete');
        Route::get('/{id?}', 'index');
    });

    Route::controller(LetterController::class)->prefix('letter')->group(function () {
        Route::post('/create', 'create');
        Route::post('/send/hybrid/{id}', 'sendHybrid');
        Route::get('/get/hybrid/{id}', 'getHybrid');
        Route::get('/get/receipt/{id}', 'getReceipt');
        Route::get('/{id?}', 'index');
    });



    Route::controller(MonitoringStatusController::class)->prefix('monitoring')->group(function () {
        Route::get('/status/{id?}', 'index');
    });

    Route::prefix('program')->group(function () {

        Route::controller(ProgramController::class)->group(function () {
            Route::post('create', 'create');
        });

        Route::prefix('monitoring')->controller(ProgramMonitoringController::class)->group(function () {
            Route::post('create', 'create');
            Route::get('{id?}', 'index');
        });

        Route::prefix('object')->controller(ProgramObjectController::class)->group(function () {
            Route::post('create', 'create');
            Route::post('attach', 'attach');
            Route::get('checklist/{id}', 'checklist');
            Route::get('{id?}', 'index');
        });

        Route::controller(ProgramController::class)->group(function () {
            Route::get('{id?}', 'index');
        });

    });


    Route::controller(ChecklistController::class)->prefix('checklist')->group(function () {
        Route::post('/create', 'create');
        Route::get('/{id?}', 'index');
    });

    Route::controller(ClaimController::class)->prefix('claim')->group(function () {
        Route::get('/count', 'count');
        Route::get('/cadastr', 'cadastr');
        Route::post('/update/{id}', 'update');
        Route::post('/create', 'create');
        Route::get('/{id?}', 'index');
    });

    Route::controller(InformationController::class)->prefix('info')->group(function () {
        Route::get('/place/{id?}', 'place');
        Route::get('/violation-type/{id?}', 'violationType');
        Route::get('/monitoring-type/{id?}', 'monitoringType');
        Route::get('/monitoring-base/{id?}', 'monitoringBase');
        Route::get('/company/{id?}', 'company');
        Route::get('monitoring-status/{id?}', 'monitoringStatus');
        Route::get('/apartment/{id?}', 'apartment');
        Route::get('/work-type/{id?}', 'workType');
    });

});
