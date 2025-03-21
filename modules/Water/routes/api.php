<?php

use Illuminate\Support\Facades\Route;
use Modules\Water\Http\Controllers\ProtocolController;
use Modules\Water\Http\Controllers\ProtocolStatusController;
use Modules\Water\Http\Controllers\ProtocolTypeController;
use Modules\Water\Http\Controllers\CardController;

Route::group(['middleware' => ['auth:api']], function () {

    Route::controller(ProtocolController::class)->prefix('protocol')->group(function () {
        Route::get('/{id?}', 'index');
        Route::post('/create/first', 'createFirst');
        Route::post('/create/second/{id}', 'createSecond');
        Route::post('/create/third/{id}', 'createThird');
    });

    Route::controller(ProtocolStatusController::class)->prefix('protocol-status')->group(function () {
        Route::get('/{id?}', 'index');
    });

    Route::controller(ProtocolTypeController::class)->prefix('protocol-type')->group(function () {
        Route::get('/{id?}', 'index');
    });

    Route::controller(CardController::class)->prefix('card')->group(function () {
        Route::get('/{id?}', 'index');
        Route::post('/register', 'register');
        Route::post('/verify', 'verify');
        Route::get('/phone', 'cardByPhone');
        Route::get('/info', 'info');
    });

});
