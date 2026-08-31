<?php

use App\Http\Controllers\AudioRecordController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\ServicePhotoController;
use App\Http\Controllers\ServiceReportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('customers', CustomerController::class)->except(['create', 'edit']);
    Route::resource('equipment', EquipmentController::class)->except(['create', 'edit']);
    Route::resource('service-orders', ServiceOrderController::class)->except(['create', 'edit']);

    Route::post('service-orders/{service_order}/audio', [AudioRecordController::class, 'store'])
        ->name('service-orders.audio');
    Route::get('audio-records/{audio_record}/status', [AudioRecordController::class, 'status'])
        ->name('audio-records.status');

    Route::put('service-reports/{service_report}', [ServiceReportController::class, 'update'])
        ->name('service-reports.update');
    Route::post('service-reports/{service_report}/finalize', [ServiceReportController::class, 'finalize'])
        ->name('service-reports.finalize');

    Route::post('service-orders/{service_order}/photos', [ServicePhotoController::class, 'store'])
        ->name('service-orders.photos');
    Route::get('service-photos/{service_photo}/file', [ServicePhotoController::class, 'file'])
        ->name('service-photos.file');
    Route::delete('service-photos/{service_photo}', [ServicePhotoController::class, 'destroy'])
        ->name('service-photos.destroy');
});

require __DIR__.'/settings.php';
