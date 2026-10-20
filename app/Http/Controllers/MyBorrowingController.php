<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Services\BorrowingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyBorrowingController extends Controller
{
    public function __construct(private BorrowingService $borrowings) {}

    public function index(Request $request): Response
    {
        $member = $request->user()->member;

        return Inertia::render('my-borrowings/index', [
            'borrowings' => $member
                ? $member->borrowings()->with('book')->latest()->get()
                : [],
        ]);
    }

    // Cancel a request that hasn't been collected yet
    public function destroy(Request $request, Borrowing $borrowing): RedirectResponse
    {
        // Readers may only cancel their own requests
        abort_unless($borrowing->member_id === $request->user()->member?->id, 403);

        return $this->attempt(fn () => $this->borrowings->cancel($borrowing), 'Request cancelled.');
    }
}
