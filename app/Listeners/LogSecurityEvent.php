<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;

class LogSecurityEvent
{
    /**
     * Log failed login attempts for security monitoring.
     */
    public function handleFailedLogin(Failed $event): void
    {
        Log::warning('Login attempt failed', [
            'email' => $event->credentials['email'] ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
