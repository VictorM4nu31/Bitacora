<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ServiceOrderController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('customers', CustomerController::class)->except(['create', 'edit']);
    Route::resource('equipment', EquipmentController::class)->except(['create', 'edit']);
    Route::resource('service-orders', ServiceOrderController::class)->except(['create', 'edit']);
});

require __DIR__.'/settings.php';
