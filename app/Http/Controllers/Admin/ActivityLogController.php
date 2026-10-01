<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by action
        if ($action = $request->string('action')->toString()) {
            $query->where('action', 'like', "%{$action}%");
        }

        // Filter by user
        if ($userId = $request->integer('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get unique actions for filter dropdown
        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('admin.activity-logs', compact('logs', 'actions'));
    }

    /**
     * Show activity log details.
     */
    public function show(ActivityLog $log): View
    {
        return view('admin.activity-log-detail', compact('log'));
    }
}
