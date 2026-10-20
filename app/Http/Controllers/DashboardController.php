<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    // One page, different content: staff see the library, readers see their books
    public function __invoke(Request $request): Response
    {
        if ($request->user()->role === 'borrower') {
            $member = $request->user()->member;

            return Inertia::render('dashboard', [
                'reader' => [
                    'loans' => $member
                        ? $member->borrowings()->active()->with('book')->latest()->get()
                        : [],
                    'fresh' => Book::with('category')->latest('id')->take(6)->get(),
                ],
            ]);
        }

        return Inertia::render('dashboard', [
            'staff' => [
                'stats' => [
                    'total' => Book::count(),
                    'available' => Book::where('availability_status', 'available')->count(),
                    'out' => Book::where('availability_status', 'borrowed')->count(),
                    'overdue' => Borrowing::overdue()->count(),
                ],
                'pending' => Borrowing::where('status', 'pending')->with(['book', 'member'])->oldest()->take(6)->get(),
                'overdue' => Borrowing::overdue()->with(['book', 'member'])->orderBy('due_date')->take(6)->get(),
            ],
        ]);
    }
}
