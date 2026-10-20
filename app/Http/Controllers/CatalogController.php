<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Services\BorrowingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(private BorrowingService $borrowings) {}

    public function index(Request $request): Response
    {
        $member = $this->member($request);
        $filters = $request->only(['search', 'category']);

        $books = Book::query()
            ->with('category')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereLike('title', "%{$search}%")
                        ->orWhereLike('author', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('catalog/index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'activeLoans' => $member->borrowings()->active()->count(),
            // Books this reader has already asked for, so their button says so
            'requestedBookIds' => $member->borrowings()->where('status', 'pending')->pluck('book_id'),
        ]);
    }

    public function borrow(Request $request, Book $book): RedirectResponse
    {
        $member = $this->member($request);

        return $this->attempt(
            fn () => $this->borrowings->request($member, $book),
            "Requested {$book->title}. Collect it at the desk once the librarian approves.",
        );
    }

    // Every reader gets a member profile when they register
    private function member(Request $request): Member
    {
        return $request->user()->member
            ?? abort(403, 'Your account has no library card yet. Ask a librarian to set it up.');
    }
}
