<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\PhotoController;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', [PhotoController::class, 'index']);
Route::get('/photo-session', fn () => Inertia::render('PhotoSession'));
Route::get('/photos/recent', [PhotoController::class, 'recent']);
Route::post('/photos/store', [PhotoController::class, 'store']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/templates.php';