<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

// Removes characters nobody types on purpose: invisible control codes and the
// Unicode direction overrides that can make text display differently from
// what it really says. Line breaks and tabs stay (descriptions use them).
class StripControlCharacters extends TransformsRequest
{
    /**
     * Passwords are hashed as typed, so they're left alone.
     *
     * @var list<string>
     */
    protected array $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected function transform($key, $value): mixed
    {
        if (! is_string($value) || in_array($key, $this->except, true)) {
            return $value;
        }

        // Broken UTF-8 becomes "?" instead of reaching the database
        $value = mb_scrub($value, 'UTF-8');

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x{200B}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $value);
    }
}
