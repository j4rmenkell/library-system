<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    // show all accounts, with search and a role filter
    public function index(Request $request): Response
    {
        // admins first, then librarians, then readers
        $query = User::orderByRaw("case role when 'admin' then 0 when 'librarian' then 1 else 2 end")
            ->orderBy('name');

        // search by name, username or email
        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereLike('name', "%{$search}%")
                    ->orWhereLike('username', "%{$search}%")
                    ->orWhereLike('email', "%{$search}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10)->withQueryString();
        $filters = $request->only(['search', 'role']);

        return Inertia::render('users/index', compact('users', 'filters'));
    }

    // show the add user form
    public function create(): Response
    {
        return Inertia::render('users/create');
    }

    // save a new account
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:admin,librarian,borrower',
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        // accounts the admin makes are trusted, so I mark the email as verified already
        // (forceCreate because email_verified_at isn't in $fillable)
        $user = User::forceCreate([...$validatedData, 'email_verified_at' => now()]);

        $this->giveReaderACard($user);

        Log::create(['user_id' => auth()->id(), 'action' => "Created user {$user->name} as ".ucfirst($user->role)]);

        return redirect()->route('users.index')->with('success', "{$user->name} can now sign in.");
    }

    // show the edit user form
    public function edit(string $id): Response
    {
        $user = User::findOrFail($id);

        return Inertia::render('users/edit', compact('user'));
    }

    // update an account
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:users,username,'.$id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,librarian,borrower',
            // nullable here: leaving it empty means "keep the old password"
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
        ]);

        // I can't change my own role, so there's always an admin left to manage accounts
        if ($user->id === auth()->id() && $validatedData['role'] !== $user->role) {
            return back()->withErrors(['role' => 'You can’t change your own role.']);
        }

        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        $this->giveReaderACard($user);

        Log::create(['user_id' => auth()->id(), 'action' => "Edited user {$user->name}"]);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    // readers need a member profile (library card) to borrow.
    // the librarian fills in the phone and address later on the Members page.
    private function giveReaderACard(User $user): void
    {
        if ($user->role === 'borrower' && ! $user->member()->exists()) {
            Member::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'contact_number' => '',
                'address' => '',
            ]);
        }
    }
}
