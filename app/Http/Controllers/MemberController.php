<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Member;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);

        $members = Member::query()
            ->withActiveLoanCount()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('contact_number', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('members/index', [
            'members' => $members,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('members/create');
    }

    public function store(MemberRequest $request): RedirectResponse
    {
        // Created here = walk-in reader, so there's no user account
        $member = Member::create($request->validated());

        ActivityLogger::log("Registered walk-in reader {$member->name}");
        Inertia::flash('toast', ['type' => 'success', 'message' => "{$member->name} can now borrow books."]);

        return to_route('members.show', $member);
    }

    public function show(Member $member): Response
    {
        return Inertia::render('members/show', [
            'member' => $member->load('user'),
            'borrowings' => $member->borrowings()->with('book')->latest()->get(),
        ]);
    }

    public function edit(Member $member): Response
    {
        return Inertia::render('members/edit', [
            'member' => $member->load('user'),
        ]);
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        ActivityLogger::log("Edited reader {$member->name}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Reader updated.']);

        return to_route('members.show', $member);
    }
}
