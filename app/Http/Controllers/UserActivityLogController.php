<?php

namespace App\Http\Controllers;

use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class UserActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = UserActivityLog::with('user')->latest('created_at');

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($user = $request->get('user')) {
            $query->where('user_name', 'LIKE', "%{$user}%");
        }
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('user-activity-logs.index', compact('logs'));
    }
}
