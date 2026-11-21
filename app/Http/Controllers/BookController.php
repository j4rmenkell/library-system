<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    // show all books, with search and filters
    public function index(Request $request): Response
    {
        // the filters come from the URL, so they get checked like form input
        $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|integer|exists:categories,id',
            'status' => 'nullable|in:available,borrowed',
        ]);

        $query = Book::with('category')->orderBy('title');

        // search by title, author or ISBN
        if ($request->search) {
            $search = $request->search;

            // ISBNs are saved as digits only, so "978-0-316" should find "9780316"
            $digits = preg_replace('/[^0-9Xx]/', '', $search);

            // whereLike ignores upper/lower case, even on Postgres
            $query->where(function ($q) use ($search, $digits) {
                $q->whereLike('title', "%{$search}%")
                    ->orWhereLike('author', "%{$search}%");

                // only check the ISBN if there are digits, or else '%%' matches every book
                if ($digits !== '') {
                    $q->orWhereLike('isbn', "%{$digits}%");
                }
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->status) {
            $query->where('availability_status', $request->status);
        }

        // withQueryString keeps the search in the page links
        $books = $query->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $filters = $request->only(['search', 'category', 'status']);

        return Inertia::render('books/index', compact('books', 'categories', 'filters'));
    }

    // show the add book form
    public function create(): Response
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('books/create', compact('categories'));
    }

    // save a new book
    public function store(Request $request): RedirectResponse
    {
        $this->cleanIsbn($request);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => ['required', 'regex:/^(\d{9}[\dX]|\d{13})$/', 'unique:books,isbn'],
            'publication_year' => 'nullable|integer|min:-3000|max:'.now()->year,
            'category_id' => 'required|exists:categories,id',
        ], $this->isbnMessages());

        // a new book always starts on the shelf
        $validatedData['availability_status'] = 'available';

        $book = Book::create($validatedData);

        Log::create(['user_id' => auth()->id(), 'action' => "Added book {$book->title}"]);

        return redirect()->route('books.index')->with('success', "{$book->title} is on the shelf.");
    }

    // show the edit book form
    public function edit(string $id): Response
    {
        $book = Book::findOrFail($id);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('books/edit', compact('book', 'categories'));
    }

    // update a book
    public function update(Request $request, string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        $this->cleanIsbn($request);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            // ignore this book's own ISBN when checking unique
            'isbn' => ['required', 'regex:/^(\d{9}[\dX]|\d{13})$/', 'unique:books,isbn,'.$id],
            'publication_year' => 'nullable|integer|min:-3000|max:'.now()->year,
            'category_id' => 'required|exists:categories,id',
        ], $this->isbnMessages());

        $book->update($validatedData);

        Log::create(['user_id' => auth()->id(), 'action' => "Edited book {$book->title}"]);

        return redirect()->route('books.index')->with('success', 'Book updated.');
    }

    // delete a book
    public function destroy(string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);

        // deleting it would erase the readers' borrowing history, so I block it
        if ($book->borrowings()->exists()) {
            return back()->with('error', "{$book->title} has borrowing history, so it can't be deleted.");
        }

        $book->delete();

        Log::create(['user_id' => auth()->id(), 'action' => "Deleted book {$book->title}"]);

        return redirect()->route('books.index')->with('success', 'Book deleted.');
    }

    // remove the dashes and spaces so "978-0-316" is saved as "9780316"
    private function cleanIsbn(Request $request): void
    {
        $request->merge([
            'isbn' => strtoupper(preg_replace('/[^0-9Xx]/', '', (string) $request->isbn)),
        ]);
    }

    // friendlier error messages for the ISBN field
    /** @return array<string, string> */
    private function isbnMessages(): array
    {
        return [
            'isbn.regex' => 'An ISBN has 10 or 13 digits. Check the number on the back cover.',
            'isbn.unique' => 'This ISBN is already in the catalog.',
        ];
    }
}
