<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LogService;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct(
        private LogService $logService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'level' => $request->get('level', 'all'),
            'search' => $request->get('search', ''),
            'lines' => $request->get('lines', 100),
        ];

        $logs = $this->logService->getFilteredLogs($filters);
        $logInfo = $this->logService->getLogFileInfo();

        return view('admin.logs.index', compact('logs', 'filters', 'logInfo'));
    }

    public function download(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');

        if (! file_exists($logFile)) {
            return redirect()->back()->with('error', 'Log file not found.');
        }

        return $this->logService->downloadLogFile();
    }

    public function clear(Request $request)
    {
        if ($this->logService->clearLogFile()) {
            return redirect()->back()->with('success', 'Log file cleared successfully.');
        }

        return redirect()->back()->with('error', 'Log file not found.');
    }

    public function tail(Request $request)
    {
        $lines = $request->get('lines', 50);
        $logs = $this->logService->getRecentLogs($lines);

        return response()->json(['logs' => $logs]);
    }
}
