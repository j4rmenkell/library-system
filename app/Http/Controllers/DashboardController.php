<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    // one page, two versions: readers see their own books, staff see the whole library
    public function index(Request $request): Response
    {
        if ($request->user()->role === 'borrower') {
            $member = $request->user()->member;

            $reader = [
                // books I asked for or have right now
                'loans' => $member
                    ? $member->borrowings()->whereIn('status', ['pending', 'borrowed'])->with('book')->latest()->get()
                    : [],
                // the 6 newest books in the library
                'fresh' => Book::with('category')->latest('id')->take(6)->get(),
            ];

            return Inertia::render('dashboard', compact('reader'));
        }

        $staff = [
            'stats' => [
                'total' => Book::count(),
                'available' => Book::where('availability_status', 'available')->count(),
                'out' => Book::where('availability_status', 'borrowed')->count(),
                'overdue' => Borrowing::where('status', 'borrowed')->whereDate('due_date', '<', today())->count(),
            ],
            // oldest requests first, since they've been waiting the longest
            'pending' => Borrowing::where('status', 'pending')->with(['book', 'member'])->oldest()->take(6)->get(),
            // the most overdue first
            'overdue' => Borrowing::where('status', 'borrowed')->whereDate('due_date', '<', today())
                ->with(['book', 'member'])->orderBy('due_date')->take(6)->get(),
        ];

        return Inertia::render('dashboard', compact('staff'));
    }
}
