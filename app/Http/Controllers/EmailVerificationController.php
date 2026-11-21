<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class EmailVerificationController extends Controller
{
    // the link in the verification email. it works without logging in,
    // because unverified readers aren't allowed to log in yet
    public function verify(string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        // the link is only good for the address it was sent to
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'Email verified. You can log in now.');
    }
}
