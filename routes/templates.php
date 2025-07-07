<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Office\TemplateController;

Route::middleware(['auth'])->prefix('office/templates')->name('templates.')->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::get('/create', [TemplateController::class, 'create'])->name('create');
    Route::post('/', [TemplateController::class, 'store'])->name('store');
    Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
    Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
});
