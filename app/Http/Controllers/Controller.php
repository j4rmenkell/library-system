<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

abstract class Controller
{
    /**
     * Run an action that may break a library rule, then go back with a toast:
     * the success message if it worked, or the rule's message if it didn't.
     * Used by one-click buttons (Approve, Borrow...) that have no form fields
     * to show an error under.
     */
    protected function attempt(callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (ValidationException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => collect($e->errors())->flatten()->first()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $success]);

        return back();
    }
}
