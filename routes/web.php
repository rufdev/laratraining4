<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManufacturerController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:super_admin'])->group(function () {
  Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
});

Route::middleware(['auth', 'verified', 'role:super_admin,inventory_manager'])->group(function () {
    
    Route::get('manufacturers', [ManufacturerController::class, 'index'])->name('manufacturers.index');
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
});

Route::middleware(['auth', 'verified', 'role:super_admin,inventory_user'])->group(function () {
    Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
