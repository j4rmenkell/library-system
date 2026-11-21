<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    // show who did what, newest first, with filters for person and dates
    public function index(Request $request): Response
    {
        $query = Log::with('user')->latest('id');

        if ($request->input('user')) {
            $query->where('user_id', $request->input('user'));
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);
        $filters = $request->only(['user', 'from', 'to']);

        return Inertia::render('logs/index', compact('logs', 'users', 'filters'));
    }
}
