<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'username' => $this->usernameRules(),
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'address' => ['required', 'string', 'max:500'],
            'password' => $this->passwordRules(),
        ])->validate();

        // Both or neither: a user without a member profile couldn't borrow
        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'borrower',
            ]);

            Member::create([
                'user_id' => $user->id,
                'name' => $input['name'],
                'contact_number' => $input['contact_number'],
                'address' => $input['address'],
            ]);

            return $user;
        });
    }
}
