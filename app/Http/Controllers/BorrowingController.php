<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Log;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BorrowingController extends Controller
{
    // show the borrowings, one tab per status
    public function index(Request $request): Response
    {
        // default to the Pending tab if the status in the URL isn't one of mine
        $tabs = ['pending', 'borrowed', 'overdue', 'returned', 'rejected'];
        $status = in_array($request->status, $tabs) ? $request->status : 'pending';

        $query = Borrowing::with(['book', 'member']);

        // overdue isn't saved in the database: it's a borrowed book that's past its due date
        if ($status === 'overdue') {
            $query->where('status', 'borrowed')->whereDate('due_date', '<', today())->orderBy('due_date');
        } else {
            $query->where('status', $status)->latest();
        }

        $borrowings = $query->paginate(10)->withQueryString();

        // the numbers on each tab
        $counts = [
            'pending' => Borrowing::where('status', 'pending')->count(),
            'borrowed' => Borrowing::where('status', 'borrowed')->count(),
            'overdue' => Borrowing::where('status', 'borrowed')->whereDate('due_date', '<', today())->count(),
            'returned' => Borrowing::where('status', 'returned')->count(),
            'rejected' => Borrowing::where('status', 'rejected')->count(),
        ];

        return Inertia::render('borrowings/index', compact('borrowings', 'status', 'counts'));
    }

    // show the walk-in form: pick a reader and a book
    public function create(): Response
    {
        // how many books each reader has right now, so the form can show "2/3"
        $members = Member::withCount([
            'borrowings as active_borrowings_count' => function ($q) {
                $q->whereIn('status', ['pending', 'borrowed']);
            },
        ])->orderBy('name')->get();

        // only books on the shelf that nobody has asked for yet
        $books = Book::where('availability_status', 'available')
            ->whereDoesntHave('borrowings', function ($q) {
                $q->where('status', 'pending');
            })
            ->orderBy('title')
            ->get(['id', 'title', 'author']);

        return Inertia::render('borrowings/create', compact('members', 'books'));
    }

    // walk-in reader at the desk: the loan starts right away (no pending step)
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'book_id' => 'required|integer|exists:books,id',
        ]);

        $member = Member::findOrFail($request->integer('member_id'));
        $book = Book::findOrFail($request->integer('book_id'));

        // the library rules. errors show under the matching field in the form
        if ($book->availability_status !== 'available') {
            return back()->withErrors(['book_id' => "{$book->title} is out on loan."]);
        }

        if ($book->borrowings()->where('status', 'pending')->exists()) {
            return back()->withErrors(['book_id' => "Someone has already asked for {$book->title}."]);
        }

        $limit = config('library.max_active_loans');

        if ($member->borrowings()->whereIn('status', ['pending', 'borrowed'])->count() >= $limit) {
            return back()->withErrors(['member_id' => "{$member->name} already has {$limit} books, the most allowed."]);
        }

        $borrowing = Borrowing::create([
            'book_id' => $book->id,
            'member_id' => $member->id,
            'status' => 'borrowed',
            'borrow_date' => today(),
            'due_date' => today()->addDays(config('library.loan_days')),
        ]);

        // the book is off the shelf now
        $book->update(['availability_status' => 'borrowed']);

        Log::create(['user_id' => auth()->id(), 'action' => "Issued {$book->title} to {$member->name} (borrowing {$borrowing->id})"]);

        return redirect()->route('borrowings.index', ['status' => 'borrowed'])
            ->with('success', "{$book->title} is due back on {$borrowing->due_date->format('M j')}.");
    }

    // the reader came to collect a book they requested
    public function approve(string $id): RedirectResponse
    {
        $borrowing = Borrowing::with(['book', 'member'])->findOrFail($id);
        $book = $borrowing->book;

        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Only waiting requests can be approved.');
        }

        if ($book->availability_status !== 'available') {
            return back()->with('error', "{$book->title} is already out on loan.");
        }

        $borrowing->update([
            'status' => 'borrowed',
            'borrow_date' => today(),
            'due_date' => today()->addDays(config('library.loan_days')),
        ]);

        $book->update(['availability_status' => 'borrowed']);

        Log::create(['user_id' => auth()->id(), 'action' => "Approved borrowing {$borrowing->id}: {$book->title} for {$borrowing->member->name}"]);

        return back()->with('success', 'Approved. The book is out on loan.');
    }

    // say no to a request. the book never left the shelf, so nothing else changes
    public function reject(string $id): RedirectResponse
    {
        $borrowing = Borrowing::with('book')->findOrFail($id);

        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Only waiting requests can be rejected.');
        }

        $borrowing->update(['status' => 'rejected']);

        Log::create(['user_id' => auth()->id(), 'action' => "Rejected borrowing {$borrowing->id}: {$borrowing->book->title}"]);

        return back()->with('success', 'Request rejected.');
    }

    // the reader brought the book back
    public function markReturned(string $id): RedirectResponse
    {
        $borrowing = Borrowing::with('book')->findOrFail($id);
        $book = $borrowing->book;

        if ($borrowing->status !== 'borrowed') {
            return back()->with('error', 'Only books out on loan can be returned.');
        }

        $borrowing->update(['status' => 'returned', 'return_date' => today()]);

        // back on the shelf
        $book->update(['availability_status' => 'available']);

        Log::create(['user_id' => auth()->id(), 'action' => "Marked borrowing {$borrowing->id} returned: {$book->title}"]);

        return back()->with('success', 'Returned. The book is back on the shelf.');
    }
}
