<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Member;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'role']);

        $users = User::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('username', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->orderByRaw("case role when 'admin' then 0 when 'librarian' then 1 else 2 end")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            // Accounts made by an admin are trusted, so no verification email
            $user = User::forceCreate([
                ...$request->safe()->only(['name', 'username', 'email', 'role', 'password']),
                'email_verified_at' => now(),
            ]);

            $this->ensureMemberProfile($user);

            return $user;
        });

        ActivityLogger::log("Created user {$user->name} as ".ucfirst($user->role));
        Inertia::flash('toast', ['type' => 'success', 'message' => "{$user->name} can now sign in."]);

        return to_route('users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('users/edit', ['user' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'username', 'email', 'role', 'password']);

        // So the library always keeps an admin who can manage accounts
        if ($user->is($request->user()) && $data['role'] !== $user->role) {
            throw ValidationException::withMessages(['role' => 'You can’t change your own role.']);
        }

        // An empty password field means "keep the current one"
        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update($data);
            $this->ensureMemberProfile($user);
        });

        ActivityLogger::log("Edited user {$user->name}");
        Inertia::flash('toast', ['type' => 'success', 'message' => 'User updated.']);

        return to_route('users.index');
    }

    // Readers need a member profile to borrow. A librarian fills in the
    // contact details later on the Members page.
    private function ensureMemberProfile(User $user): void
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
