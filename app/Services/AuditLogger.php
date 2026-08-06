<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an audit log entry.
     *
     * @param string $action e.g. 'login', 'product_created', 'customer_deleted'
     * @param string $description Human-readable summary of what happened
     */
    public static function log(string $action, string $description): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Unknown',
            'user_role' => $user?->role,
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
