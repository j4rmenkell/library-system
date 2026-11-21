<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    // show all readers, with a search
    public function index(Request $request): Response
    {
        // count only the books they asked for or still have (pending + borrowed)
        $query = Member::withCount([
            'borrowings as active_borrowings_count' => function ($q) {
                $q->whereIn('status', ['pending', 'borrowed']);
            },
        ])->orderBy('name');

        // search by name or phone number
        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereLike('name', "%{$search}%")
                    ->orWhereLike('contact_number', "%{$search}%");
            });
        }

        $members = $query->paginate(10)->withQueryString();
        $filters = $request->only(['search']);

        return Inertia::render('members/index', compact('members', 'filters'));
    }

    // show the add reader form
    public function create(): Response
    {
        return Inertia::render('members/create');
    }

    // save a new walk-in reader (no user account, just a library card)
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate($this->rules(), $this->messages());

        $member = Member::create($validatedData);

        Log::create(['user_id' => auth()->id(), 'action' => "Registered walk-in reader {$member->name}"]);

        return redirect()->route('members.show', $member->id)->with('success', "{$member->name} can now borrow books.");
    }

    // show one reader and everything they've borrowed
    public function show(string $id): Response
    {
        $member = Member::with('user')->findOrFail($id);
        $borrowings = $member->borrowings()->with('book')->latest()->get();

        return Inertia::render('members/show', compact('member', 'borrowings'));
    }

    // show the edit reader form
    public function edit(string $id): Response
    {
        $member = Member::with('user')->findOrFail($id);

        return Inertia::render('members/edit', compact('member'));
    }

    // update a reader
    public function update(Request $request, string $id): RedirectResponse
    {
        $member = Member::findOrFail($id);

        $validatedData = $request->validate($this->rules(), $this->messages());

        $member->update($validatedData);

        Log::create(['user_id' => auth()->id(), 'action' => "Edited reader {$member->name}"]);

        return redirect()->route('members.show', $member->id)->with('success', 'Reader updated.');
    }

    // same rules for adding and editing, so I keep them in one place
    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // digits, spaces, +, - and brackets: "0917 555 0123" or "+63 917 555 0123"
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'address' => 'required|string|max:500',
        ];
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return [
            'contact_number.regex' => 'Use digits only, like 0917 555 0123.',
        ];
    }
}
