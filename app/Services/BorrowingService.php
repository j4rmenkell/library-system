<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Every change to a borrowing goes through here, so the rules live in one
 * place: the loan limit, one request per book, and keeping each book's
 * availability_status in step with its borrowing.
 *
 * Each method runs in a transaction and locks the book row, so two
 * librarians clicking at the same moment can't lend the same copy twice.
 */
class BorrowingService
{
    // A reader asks for a book from the catalog. The librarian approves later.
    public function request(Member $member, Book $book): Borrowing
    {
        return DB::transaction(function () use ($member, $book) {
            $book = Book::lockForUpdate()->findOrFail($book->id);

            $this->ensureCanTake($member, $book);

            $borrowing = Borrowing::create([
                'book_id' => $book->id,
                'member_id' => $member->id,
                'status' => 'pending',
            ]);

            ActivityLogger::log("Requested {$book->title} (borrowing {$borrowing->id})");

            return $borrowing;
        });
    }

    // A walk-in reader at the desk: the loan starts straight away.
    public function issue(Member $member, Book $book): Borrowing
    {
        return DB::transaction(function () use ($member, $book) {
            $book = Book::lockForUpdate()->findOrFail($book->id);

            $this->ensureCanTake($member, $book);

            $borrowing = Borrowing::create([
                'book_id' => $book->id,
                'member_id' => $member->id,
                'status' => 'borrowed',
                'borrow_date' => today(),
                'due_date' => today()->addDays(config('library.loan_days')),
            ]);

            $book->update(['availability_status' => 'borrowed']);

            ActivityLogger::log("Issued {$book->title} to {$member->name} (borrowing {$borrowing->id})");

            return $borrowing;
        });
    }

    // The reader is at the desk to collect a requested book.
    public function approve(Borrowing $borrowing): void
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);
            $book = Book::lockForUpdate()->findOrFail($borrowing->book_id);

            $this->ensureStatus($borrowing, 'pending', 'Only waiting requests can be approved.');

            if ($book->availability_status !== 'available') {
                throw ValidationException::withMessages(['borrowing' => "{$book->title} is already out on loan."]);
            }

            $borrowing->update([
                'status' => 'borrowed',
                'borrow_date' => today(),
                'due_date' => today()->addDays(config('library.loan_days')),
            ]);
            $book->update(['availability_status' => 'borrowed']);

            ActivityLogger::log("Approved borrowing {$borrowing->id}: {$book->title} for {$borrowing->member->name}");
        });
    }

    public function reject(Borrowing $borrowing): void
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            $this->ensureStatus($borrowing, 'pending', 'Only waiting requests can be rejected.');

            $borrowing->update(['status' => 'rejected']);

            ActivityLogger::log("Rejected borrowing {$borrowing->id}: {$borrowing->book->title}");
        });
    }

    public function markReturned(Borrowing $borrowing): void
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);
            $book = Book::lockForUpdate()->findOrFail($borrowing->book_id);

            $this->ensureStatus($borrowing, 'borrowed', 'Only books out on loan can be returned.');

            $borrowing->update(['status' => 'returned', 'return_date' => today()]);
            $book->update(['availability_status' => 'available']);

            ActivityLogger::log("Marked borrowing {$borrowing->id} returned: {$book->title}");
        });
    }

    // A reader changes their mind before collecting the book.
    public function cancel(Borrowing $borrowing): void
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            $this->ensureStatus($borrowing, 'pending', 'Only waiting requests can be cancelled.');

            ActivityLogger::log("Cancelled request for {$borrowing->book->title} (borrowing {$borrowing->id})");

            $borrowing->delete();
        });
    }

    // The rules for taking a book, shared by request() and issue()
    private function ensureCanTake(Member $member, Book $book): void
    {
        if ($book->availability_status !== 'available') {
            throw ValidationException::withMessages(['book_id' => "{$book->title} is out on loan."]);
        }

        if ($book->borrowings()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages(['book_id' => "Someone has already asked for {$book->title}."]);
        }

        $limit = config('library.max_active_loans');

        if ($member->borrowings()->active()->count() >= $limit) {
            throw ValidationException::withMessages(['member_id' => "{$member->name} already has {$limit} books, the most allowed."]);
        }
    }

    private function ensureStatus(Borrowing $borrowing, string $status, string $message): void
    {
        if ($borrowing->status !== $status) {
            throw ValidationException::withMessages(['borrowing' => $message]);
        }
    }
}
