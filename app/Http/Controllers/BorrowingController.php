<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Services\BorrowingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BorrowingController extends Controller
{
    private const TABS = ['pending', 'borrowed', 'overdue', 'returned', 'rejected'];

    public function __construct(private BorrowingService $borrowings) {}

    public function index(Request $request): Response
    {
        $status = in_array($request->query('status'), self::TABS, true)
            ? $request->query('status')
            : 'pending';

        $query = Borrowing::query()->with(['book', 'member']);

        // "Out on loan" includes overdue books; the Overdue tab shows only those
        $status === 'overdue'
            ? $query->overdue()->orderBy('due_date')
            : $query->where('status', $status)->latest();

        return Inertia::render('borrowings/index', [
            'borrowings' => $query->paginate(10)->withQueryString(),
            'status' => $status,
            'counts' => [
                'pending' => Borrowing::where('status', 'pending')->count(),
                'borrowed' => Borrowing::where('status', 'borrowed')->count(),
                'overdue' => Borrowing::overdue()->count(),
                'returned' => Borrowing::where('status', 'returned')->count(),
                'rejected' => Borrowing::where('status', 'rejected')->count(),
            ],
        ]);
    }

    // Walk-in issue: pick a reader and a book that's free to lend
    public function create(): Response
    {
        return Inertia::render('borrowings/create', [
            'members' => Member::withActiveLoanCount()->orderBy('name')->get(),
            'books' => Book::where('availability_status', 'available')
                ->whereDoesntHave('borrowings', fn ($q) => $q->where('status', 'pending'))
                ->orderBy('title')
                ->get(['id', 'title', 'author']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')],
            'book_id' => ['required', 'integer', Rule::exists('books', 'id')],
        ]);

        // Rule problems (limit reached, book taken) show under the fields
        $borrowing = $this->borrowings->issue(
            Member::findOrFail($request->integer('member_id')),
            Book::findOrFail($request->integer('book_id')),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$borrowing->book->title} is due back on {$borrowing->due_date->format('M j')}.",
        ]);

        return to_route('borrowings.index', ['status' => 'borrowed']);
    }

    public function approve(Borrowing $borrowing): RedirectResponse
    {
        return $this->attempt(fn () => $this->borrowings->approve($borrowing), 'Approved. The book is out on loan.');
    }

    public function reject(Borrowing $borrowing): RedirectResponse
    {
        return $this->attempt(fn () => $this->borrowings->reject($borrowing), 'Request rejected.');
    }

    public function markReturned(Borrowing $borrowing): RedirectResponse
    {
        return $this->attempt(fn () => $this->borrowings->markReturned($borrowing), 'Returned. The book is back on the shelf.');
    }
}
