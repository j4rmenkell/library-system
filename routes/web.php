<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MyBorrowingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Librarians and admins run the library
    Route::middleware('role:admin,librarian')->group(function () {
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('books', BookController::class)->except(['show']);
        Route::resource('members', MemberController::class)->except(['destroy']);

        Route::resource('borrowings', BorrowingController::class)->only(['index', 'create', 'store']);
        Route::patch('borrowings/{borrowing}/approve', [BorrowingController::class, 'approve'])->name('borrowings.approve');
        Route::patch('borrowings/{borrowing}/reject', [BorrowingController::class, 'reject'])->name('borrowings.reject');
        Route::patch('borrowings/{borrowing}/return', [BorrowingController::class, 'markReturned'])->name('borrowings.mark-returned');
    });

    // Only admins manage accounts and read the logs
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');
    });

    // Readers browse and borrow
    Route::middleware('role:borrower')->group(function () {
        Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
        Route::post('catalog/{book}/borrow', [CatalogController::class, 'borrow'])->name('catalog.borrow');
        Route::get('my-borrowings', [MyBorrowingController::class, 'index'])->name('my-borrowings.index');
        Route::delete('my-borrowings/{borrowing}', [MyBorrowingController::class, 'destroy'])->name('my-borrowings.destroy');
    });

});

require __DIR__.'/settings.php';
