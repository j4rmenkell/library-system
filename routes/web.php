<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {


    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:admin,librarian')->group(function () {
        
        
    });

    Route::middleware('role:admin')->group(function () {
        

    });

    Route::middleware('role:borrower')->group(function () {
        

    });


});

require __DIR__.'/settings.php';
