<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query();

        if ($date = $request->string('date')->trim()->value()) {
            $query->whereDate('created_at', $date);
        }

        if ($action = $request->string('action')->trim()->value()) {
            $query->where('action', $action);
        }

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($query) use ($search) {
                $query->where('user_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $auditLogs = $query->latest('created_at')->paginate(25)->withQueryString();
        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('audit-logs.index', compact('auditLogs', 'actions'));
    }
}
