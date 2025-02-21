<?php

use Illuminate\Support\Facades\Route;

Route::get('abs', fn() => 'Salom')->middleware('auth:api');
