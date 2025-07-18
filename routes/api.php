<?php

use Illuminate\Support\Facades\Route;

Route::post('/import-requests', [App\Http\Controllers\ImportRequestController::class, 'store'])->name('import-requests.store');

Route::get('/import-requests', [App\Http\Controllers\ImportRequestController::class, 'get'])->name('import-requests.get');
