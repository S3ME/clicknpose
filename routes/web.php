<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\PhotoSessionController;
use App\Models\Template;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\Api\QRCodeController;
use App\Http\Controllers\Api\DownloadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (admin only)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Landing page
Route::get('/', [PhotoController::class, 'index'])->name('home');

// Photo upload & session flow
Route::prefix('photos')->group(function () {
    Route::get('/recent', [PhotoController::class, 'recent'])->name('photos.recent');
    Route::post('/store', [PhotoController::class, 'store'])->name('photos.store');
});

// Template selection + photo session entry
Route::get('/select-template', function () {
    return Inertia::render('SelectTemplate', [
        'templates' => Template::all(),
        'photo_path' => request('photo_path'),
    ]);
})->name('select-template');

Route::get('/photo-session', [PhotoController::class, 'renderTemplate'])->name('photo.session');

// Session actions
Route::post('/session/finish', [PhotoSessionController::class, 'finish'])->name('session.finish');
Route::get('/session/download/{slug}', [PhotoSessionController::class, 'download'])->name('session.download');
Route::get('/preview/{session:session_code}', [PhotoSessionController::class, 'preview'])->name('session.preview');

Route::get('/download/{filename}', function ($filename) {
    $path = storage_path('app/public/images/result/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
})->name('download.image');

Route::get('/stream/preview', [StreamController::class, 'preview']);
Route::post('/ffmpeg/start', [StreamController::class, 'start']);
Route::post('/ffmpeg/stop', [StreamController::class, 'stop']);

Route::get('/photographer', function () {
    return Inertia::render('PhotographerClient');
});

// Additional config files
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/templates.php';
