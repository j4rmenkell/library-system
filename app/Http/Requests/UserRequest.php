<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // The user being edited, or null when creating one
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : null;

        return [
            'name' => $this->nameRules(),
            'username' => $this->usernameRules($userId),
            'email' => $this->emailRules($userId),
            'role' => ['required', Rule::in(['admin', 'librarian', 'borrower'])],
            // Required for a new user; leave empty when editing to keep it
            'password' => [$userId ? 'nullable' : 'required', 'string', Password::default(), 'confirmed'],
        ];
    }
}
