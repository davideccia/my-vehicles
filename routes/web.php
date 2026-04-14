<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleRefuelController;
use App\Http\Controllers\VehicleServiceController;
use App\Http\Controllers\VehicleServiceReminderController;
use App\Http\Controllers\VehicleServiceTypeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/vehicles');

Route::resource('vehicles', VehicleController::class);

Route::resource('vehicle-refuels', VehicleRefuelController::class);

Route::resource('vehicle-service-types', VehicleServiceTypeController::class);

Route::resource('vehicle-services', VehicleServiceController::class);
Route::resource('vehicle-service-reminders', VehicleServiceReminderController::class)->except(['show']);

Route::get('/reports', [ReportController::class, 'show'])->name('reports.show');

Route::get('/settings', [SettingsController::class, 'show'])->name('settings.show');
Route::post('/settings/locale', [SettingsController::class, 'updateLocale'])->name('settings.locale');
Route::post('/settings/color', [SettingsController::class, 'updateColor'])->name('settings.color');
Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
