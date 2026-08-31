<?php

use App\Http\Controllers\AudioRecordController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\ServicePhotoController;
use App\Http\Controllers\ServiceReportController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', SetLocale::class])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::patch('locale', [LocaleController::class, 'update'])->name('locale.update');

    Route::resource('customers', CustomerController::class)->except(['create', 'edit']);
    Route::resource('equipment', EquipmentController::class)->except(['create', 'edit']);
    Route::resource('service-orders', ServiceOrderController::class)->except(['create', 'edit']);

    Route::post('equipment/{equipment}/maintenance', [MaintenanceScheduleController::class, 'store'])
        ->name('equipment.maintenance');
    Route::post('maintenance-schedules/{maintenance_schedule}/complete', [MaintenanceScheduleController::class, 'complete'])
        ->name('maintenance-schedules.complete');

    Route::post('service-orders/{service_order}/audio', [AudioRecordController::class, 'store'])
        ->name('service-orders.audio');
    Route::get('audio-records/{audio_record}/status', [AudioRecordController::class, 'status'])
        ->name('audio-records.status');

    Route::post('service-orders/{service_order}/photos', [ServicePhotoController::class, 'store'])
        ->name('service-orders.photos');
    Route::get('service-photos/{service_photo}/file', [ServicePhotoController::class, 'file'])
        ->name('service-photos.file');
    Route::delete('service-photos/{service_photo}', [ServicePhotoController::class, 'destroy'])
        ->name('service-photos.destroy');

    Route::put('service-reports/{service_report}', [ServiceReportController::class, 'update'])
        ->name('service-reports.update');
    Route::post('service-reports/{service_report}/finalize', [ServiceReportController::class, 'finalize'])
        ->name('service-reports.finalize');
    Route::get('service-reports/{service_report}/pdf', [ServiceReportController::class, 'pdf'])
        ->name('service-reports.pdf');
    Route::get('service-reports/{service_report}/share', [ServiceReportController::class, 'share'])
        ->name('service-reports.share');
});

Route::middleware('signed')->group(function () {
    Route::get('shared/{service_report}', [ServiceReportController::class, 'shared'])
        ->name('reports.shared');
});

Route::get('mobile/login', [MobileController::class, 'login'])->name('mobile.login');
Route::post('mobile/login', [MobileController::class, 'submitLogin'])->name('mobile.login.submit');

Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/', [MobileController::class, 'index'])->name('index');
    Route::get('services/{service_order}', [MobileController::class, 'show'])->name('show');
    Route::post('logout', [MobileController::class, 'logout'])->name('logout');
});

require __DIR__.'/settings.php';
