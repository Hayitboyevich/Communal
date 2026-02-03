<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

Route::get('protocol-pdf/{id}', [PdfController::class, 'protocolPdf']);
Route::get('monitoring-pdf/{id}', [PdfController::class, 'monitoringPdf']);
Route::get('presentation-pdf/{id}', [PdfController::class, 'presentationPdf']);
Route::get('monitoring-excel/{id}', [PdfController::class, 'monitoringExcel']);
Route::get('protocol-excel/{id}', [PdfController::class, 'protocolExcel']);


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/debug-sentry', function () {
    throw new Exception('Sentry test xatosi!');
});


require __DIR__.'/auth.php';
