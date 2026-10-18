<?php

namespace App\Support;

use App\Models\Log as ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    // Usage: ActivityLogger::log('Added book Circe');
    public static function log(string $action): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
        ]);
    }
}
