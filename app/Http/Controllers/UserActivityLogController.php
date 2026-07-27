<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class UserActivityLogController extends Controller
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['user_name', 'action', 'ip_address', 'created_at'];

    private function filteredLogsQuery(Request $request)
    {
        $query = UserActivityLog::with('user');

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

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredLogsQuery($request);
        $logs = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'created_at', 'desc')
            ->paginate(50)->withQueryString();

        return view('user-activity-logs.index', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? UserActivityLog::with('user')->whereIn('id', $request->input('ids'))
            : $this->filteredLogsQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'created_at', 'desc')
            ->get()
            ->map(fn (UserActivityLog $l) => [
                'time' => $l->created_at->format('Y-m-d H:i:s'),
                'user' => $l->user_name,
                'action' => $l->actionLabel(),
                'ip' => $l->ip_address,
                'user_agent' => $l->user_agent,
            ]);

        return $this->streamCsvExport('user-activity-logs-' . now()->format('Ymd_His') . '.csv', [
            'time' => 'Time', 'user' => 'User', 'action' => 'Action', 'ip' => 'IP Address', 'user_agent' => 'User Agent',
        ], $rows);
    }
}
