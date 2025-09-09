<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Apply filters
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->model_type) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $auditLogs = $query->latest()->paginate(20);
        $users = User::orderBy('name')->get();
        
        $modelTypes = AuditLog::select('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->pluck('model_type');

        return view('audit-logs.index', compact('auditLogs', 'users', 'modelTypes'));
    }
}