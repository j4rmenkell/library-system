<?php

namespace App\Listeners;

use App\Models\Log as ActivityLog;
use Illuminate\Auth\Events\Login;

// Laravel finds this automatically because it handles the Login event
class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'action' => 'Signed in',
        ]);
    }
}
