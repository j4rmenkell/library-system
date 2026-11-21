<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyBorrowingController extends Controller
{
    // show only the logged-in reader's own borrowings
    public function index(Request $request): Response
    {
        $member = $request->user()->member;

        $borrowings = $member ? $member->borrowings()->with('book')->latest()->get() : [];

        return Inertia::render('my-borrowings/index', compact('borrowings'));
    }

    // cancel a request before collecting the book (this deletes the row)
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $borrowing = Borrowing::with('book')->findOrFail($id);

        // readers can only cancel their own requests
        if ($borrowing->member_id !== $request->user()->member?->id) {
            abort(403);
        }

        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Only waiting requests can be cancelled.');
        }

        Log::create(['user_id' => auth()->id(), 'action' => "Cancelled request for {$borrowing->book->title} (borrowing {$borrowing->id})"]);

        $borrowing->delete();

        return back()->with('success', 'Request cancelled.');
    }
}
