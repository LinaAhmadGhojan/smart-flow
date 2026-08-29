<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/company-info.json', [SettingsController::class, 'companyInfoJson']);

Route::get('/login', fn () => redirect('/admin'))->name('login');

// Catch-all route for Vue Router (must be last)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
