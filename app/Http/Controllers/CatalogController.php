<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    // the reader's view of the books, with search and a category filter
    public function index(Request $request): Response
    {
        // the filters come from the URL, so they get checked like form input
        $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|integer|exists:categories,id',
        ]);

        // every reader gets a member profile when they sign up, so this shouldn't happen
        $member = $request->user()->member;

        if (! $member) {
            abort(403, 'Your account has no library card yet. Ask a librarian to set it up.');
        }

        $query = Book::with('category')->orderBy('title');

        // search by title or author
        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereLike('title', "%{$search}%")
                    ->orWhereLike('author', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        $books = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $filters = $request->only(['search', 'category']);

        // how many books I have or asked for (the limit is 3)
        $activeLoans = $member->borrowings()->whereIn('status', ['pending', 'borrowed'])->count();

        // books I already asked for, so their button says "Requested"
        $requestedBookIds = $member->borrowings()->where('status', 'pending')->pluck('book_id');

        return Inertia::render('catalog/index', compact('books', 'categories', 'filters', 'activeLoans', 'requestedBookIds'));
    }

    // the reader asks for a book. it stays on the shelf until the librarian approves
    public function borrow(Request $request, string $id): RedirectResponse
    {
        $member = $request->user()->member;

        if (! $member) {
            abort(403, 'Your account has no library card yet. Ask a librarian to set it up.');
        }

        $book = Book::findOrFail($id);

        // the library rules (same ones the walk-in form checks)
        if ($book->availability_status !== 'available') {
            return back()->with('error', "{$book->title} is out on loan.");
        }

        if ($book->borrowings()->where('status', 'pending')->exists()) {
            return back()->with('error', "Someone has already asked for {$book->title}.");
        }

        $limit = config('library.max_active_loans');

        if ($member->borrowings()->whereIn('status', ['pending', 'borrowed'])->count() >= $limit) {
            return back()->with('error', "You already have {$limit} books, the most allowed.");
        }

        $borrowing = Borrowing::create([
            'book_id' => $book->id,
            'member_id' => $member->id,
            'status' => 'pending',
        ]);

        Log::create(['user_id' => auth()->id(), 'action' => "Requested {$book->title} (borrowing {$borrowing->id})"]);

        return back()->with('success', "Requested {$book->title}. Collect it at the desk once the librarian approves.");
    }
}
