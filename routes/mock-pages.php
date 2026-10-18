<?php

/*
|--------------------------------------------------------------------------
| TEMPORARY front-end routes
|--------------------------------------------------------------------------
|
| Every page can be opened before its controller exists. Each route serves
| sample data from App\Support\MockLibrary in the same shape a controller
| will send, and forms "save" by flashing a toast and redirecting.
|
| When you build a controller, delete its block here and add the real route
| to web.php with THE SAME NAME (e.g. books.index), so the pages keep working.
| Delete this file and MockLibrary.php when every block is gone.
|
*/

use App\Support\MockLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Pretends to save, so forms can be tried out before the controller exists
$pretend = function (string $message, ?string $redirectRoute = null) {
    return function () use ($message, $redirectRoute) {
        Inertia::flash('toast', ['type' => 'info', 'message' => $message.' (sample data, not saved yet)']);

        return $redirectRoute ? to_route($redirectRoute) : back();
    };
};

// --- Landing page (public) ---------------------------------------------------
Route::get('/', fn () => Inertia::render('welcome', [
    'books' => collect(MockLibrary::books())->whereNotNull('cover_url')->values(),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () use ($pretend) {

    // --- Dashboard (one page, different content per role) -----------------
    Route::get('dashboard', function (Request $request) {
        $borrowings = collect(MockLibrary::borrowings());

        if ($request->user()->role === 'borrower') {
            return Inertia::render('dashboard', [
                'reader' => [
                    'loans' => $borrowings->where('member_id', 1)->whereIn('status', ['pending', 'borrowed'])->values(),
                    'fresh' => collect(MockLibrary::books())->take(6)->values(),
                ],
            ]);
        }

        $books = collect(MockLibrary::books());
        $overdue = $borrowings->filter(fn ($b) => $b['status'] === 'borrowed' && $b['due_date'] < now()->toDateString());

        return Inertia::render('dashboard', [
            'staff' => [
                'stats' => [
                    'total' => $books->count(),
                    'available' => $books->where('availability_status', 'available')->count(),
                    'out' => $books->where('availability_status', 'borrowed')->count(),
                    'overdue' => $overdue->count(),
                ],
                'pending' => $borrowings->where('status', 'pending')->values(),
                'overdue' => $overdue->values(),
            ],
        ]);
    })->name('dashboard');

    // --- Librarian + admin -----------------------------------------------
    Route::middleware('role:admin,librarian')->group(function () use ($pretend) {

        // Categories

        // Books
        Route::get('books', fn (Request $request) => Inertia::render('books/index', [
            'books' => MockLibrary::paginate(MockLibrary::books(), $request),
            'categories' => MockLibrary::categories(),
            'filters' => $request->only(['search', 'category', 'status']),
        ]))->name('books.index');
        Route::get('books/create', fn () => Inertia::render('books/create', [
            'categories' => MockLibrary::categories(),
        ]))->name('books.create');
        Route::post('books', $pretend('Book added', 'books.index'))->name('books.store');
        Route::get('books/{book}/edit', fn (int $book) => Inertia::render('books/edit', [
            'book' => MockLibrary::book($book),
            'categories' => MockLibrary::categories(),
        ]))->name('books.edit');
        Route::put('books/{book}', $pretend('Book updated', 'books.index'))->name('books.update');
        Route::delete('books/{book}', $pretend('Book deleted', 'books.index'))->name('books.destroy');

        // Members
        Route::get('members', fn (Request $request) => Inertia::render('members/index', [
            'members' => MockLibrary::paginate(MockLibrary::members(), $request),
            'filters' => $request->only(['search']),
        ]))->name('members.index');
        Route::get('members/create', fn () => Inertia::render('members/create'))->name('members.create');
        Route::post('members', $pretend('Reader registered', 'members.index'))->name('members.store');
        Route::get('members/{member}', fn (int $member) => Inertia::render('members/show', [
            'member' => MockLibrary::member($member),
            'borrowings' => collect(MockLibrary::borrowings())->where('member_id', $member)->values(),
        ]))->name('members.show');
        Route::get('members/{member}/edit', fn (int $member) => Inertia::render('members/edit', [
            'member' => MockLibrary::member($member),
        ]))->name('members.edit');
        Route::put('members/{member}', $pretend('Reader updated', 'members.index'))->name('members.update');

        // Borrowings
        Route::get('borrowings', function (Request $request) {
            $status = $request->query('status', 'pending');
            $all = collect(MockLibrary::borrowings());
            $today = now()->toDateString();
            $isOverdue = fn ($b) => $b['status'] === 'borrowed' && $b['due_date'] < $today;

            $rows = match ($status) {
                'overdue' => $all->filter($isOverdue),
                default => $all->where('status', $status),
            };

            return Inertia::render('borrowings/index', [
                'borrowings' => MockLibrary::paginate($rows->values()->all(), $request),
                'status' => $status,
                'counts' => [
                    'pending' => $all->where('status', 'pending')->count(),
                    'borrowed' => $all->where('status', 'borrowed')->count(),
                    'overdue' => $all->filter($isOverdue)->count(),
                    'returned' => $all->where('status', 'returned')->count(),
                    'rejected' => $all->where('status', 'rejected')->count(),
                ],
            ]);
        })->name('borrowings.index');
        Route::get('borrowings/create', fn () => Inertia::render('borrowings/create', [
            'members' => MockLibrary::members(),
            'books' => collect(MockLibrary::books())->where('availability_status', 'available')->values(),
        ]))->name('borrowings.create');
        Route::post('borrowings', $pretend('Book issued', 'borrowings.index'))->name('borrowings.store');
        Route::patch('borrowings/{borrowing}/approve', $pretend('Request approved'))->name('borrowings.approve');
        Route::patch('borrowings/{borrowing}/reject', $pretend('Request rejected'))->name('borrowings.reject');
        Route::patch('borrowings/{borrowing}/return', $pretend('Marked as returned'))->name('borrowings.mark-returned');
    });

    // --- Admin only --------------------------------------------------------
    Route::middleware('role:admin')->group(function () use ($pretend) {

        // Users
        Route::get('users', fn (Request $request) => Inertia::render('users/index', [
            'users' => MockLibrary::paginate(MockLibrary::users(), $request),
            'filters' => $request->only(['search', 'role']),
        ]))->name('users.index');
        Route::get('users/create', fn () => Inertia::render('users/create'))->name('users.create');
        Route::post('users', $pretend('User created', 'users.index'))->name('users.store');
        Route::get('users/{user}/edit', fn (int $user) => Inertia::render('users/edit', [
            'user' => MockLibrary::user($user),
        ]))->name('users.edit');
        Route::put('users/{user}', $pretend('User updated', 'users.index'))->name('users.update');

        // Activity logs
        Route::get('logs', fn (Request $request) => Inertia::render('logs/index', [
            'logs' => MockLibrary::paginate(MockLibrary::logs(), $request),
            'users' => collect(MockLibrary::users())->map->only(['id', 'name']),
            'filters' => $request->only(['user', 'from', 'to']),
        ]))->name('logs.index');
    });

    // --- Readers -------------------------------------------------------------
    Route::middleware('role:borrower')->group(function () use ($pretend) {
        Route::get('catalog', function (Request $request) {
            $books = collect(MockLibrary::books());

            if ($category = $request->query('category')) {
                $books = $books->where('category_id', (int) $category);
            }

            return Inertia::render('catalog/index', [
                'books' => MockLibrary::paginate($books->values()->all(), $request, 12),
                'categories' => MockLibrary::categories(),
                'filters' => $request->only(['search', 'category']),
                'activeLoans' => 2,
                'requestedBookIds' => [4],
            ]);
        })->name('catalog.index');
        Route::post('catalog/{book}/borrow', $pretend('Request sent to the librarian'))->name('catalog.borrow');

        Route::get('my-borrowings', fn () => Inertia::render('my-borrowings/index', [
            'borrowings' => collect(MockLibrary::borrowings())->where('member_id', 1)->values(),
        ]))->name('my-borrowings.index');
        Route::delete('my-borrowings/{borrowing}', $pretend('Request cancelled'))->name('my-borrowings.destroy');
    });
});
