<?php

use Illuminate\Support\Facades\Route;
use Modules\Water\Http\Controllers\ProtocolController;
use Modules\Water\Http\Controllers\ProtocolStatusController;
use Modules\Water\Http\Controllers\ProtocolTypeController;
use Modules\Water\Http\Controllers\CardController;
use App\Http\Controllers\Api\AuthController;



Route::group(['middleware' => ['auth:api']], function () {

    Route::controller(ProtocolController::class)->prefix('protocol')->group(function () {
        Route::post('/attach', 'attach');
        Route::get('/count', 'count');
        Route::post('/reject', 'reject');
        Route::post('/send/defect', 'sendDefect');
        Route::post('/create/first', 'createFirst');
        Route::post('/create/second/{id}', 'createSecond');
        Route::post('/create/third/{id}', 'createThird');
        Route::post('/confirm/defect', 'confirmDefect');
        Route::post('/reject/defect', 'rejectDefect');
        Route::get('/{id?}', 'index');
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
        Route::post('/create', 'create');
        Route::post('/change', 'change');
    });

});
