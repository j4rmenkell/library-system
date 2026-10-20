<?php

namespace App\Http\Controllers;

use App\Models\Log as ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['user', 'from', 'to']);

        $logs = ActivityLog::query()
            ->with('user')
            ->when($filters['user'] ?? null, fn ($query, $user) => $query->where('user_id', $user))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('logs/index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}
