<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::middleware('role:admin,librarian')->group(function () {
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('books', BookController::class)->except(['show']);
    });

    Route::middleware('role:admin')->group(function () {
        // Real controller routes go here: users, logs
    });

    Route::middleware('role:borrower')->group(function () {
        // Real controller routes go here: catalog, my-borrowings
    });

});

// TEMPORARY: sample-data routes so every page opens before its controller
// exists. Remove each block as you build the real controller.
require __DIR__.'/mock-pages.php';

require __DIR__.'/settings.php';
