<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category', 'status']);

        $books = Book::query()
            ->with('category')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                // ISBNs are stored as digits only, so "978-0-316" searches "9780316"
                $digits = preg_replace('/[^0-9Xx]/', '', $search);

                $query->where(function ($query) use ($search, $digits) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");

                    // Only when the search has digits: otherwise '%%' would match every book
                    if ($digits !== '') {
                        $query->orWhere('isbn', 'like', "%{$digits}%");
                    }
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('availability_status', $status))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('books/index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('books/create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $book = Book::create([
            ...$request->validated(),
            'availability_status' => 'available',
        ]);

        ActivityLogger::log("Added book {$book->title}");
        Inertia::flash('toast', ['type' => 'success', 'message' => "{$book->title} is on the shelf."]);

        return to_route('books.index');
    }

    public function edit(Book $book): Response
    {
        return Inertia::render('books/edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $book->update($request->validated());

        ActivityLogger::log("Edited book {$book->title}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Book updated.']);

        return to_route('books.index');
    }

    public function destroy(Book $book): RedirectResponse
    {
        // Deleting would erase readers' borrowing history, so it's blocked
        if ($book->borrowings()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => "{$book->title} has borrowing history, so it can't be deleted."]);

            return back();
        }

        $book->delete();

        ActivityLogger::log("Deleted book {$book->title}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Book deleted.']);

        return to_route('books.index');
    }
}
