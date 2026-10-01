<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log a user activity.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function log(string $action, ?string $description = null, ?array $metadata = null): void
    {
        $request = request();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log authentication events.
     */
    public static function logAuth(string $action, ?string $description = null): void
    {
        self::log("auth.{$action}", $description);
    }

    /**
     * Log reading activity.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function logReading(string $action, ?string $description = null, ?array $metadata = null): void
    {
        self::log("reading.{$action}", $description, $metadata);
    }

    /**
     * Log admin actions.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function logAdmin(string $action, ?string $description = null, ?array $metadata = null): void
    {
        self::log("admin.{$action}", $description, $metadata);
    }
}
