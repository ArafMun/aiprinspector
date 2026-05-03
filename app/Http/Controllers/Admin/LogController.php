<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        $filters = [
            'level' => $request->get('level', 'all'),
            'search' => $request->get('search', ''),
            'lines' => $request->get('lines', 100),
        ];

        if (file_exists($logFile)) {
            $content = File::get($logFile);
            $lines = array_reverse(explode("\n", $content));

            // Apply filters
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                // Skip if doesn't match search
                if (!empty($filters['search']) && !str_contains(strtolower($line), strtolower($filters['search']))) {
                    continue;
                }

                // Filter by log level
                if ($filters['level'] !== 'all') {
                    $levelMatch = false;
                    switch ($filters['level']) {
                        case 'error':
                            if (str_contains($line, '.ERROR') || str_contains($line, '.CRITICAL')) {
                                $levelMatch = true;
                            }
                            break;
                        case 'warning':
                            if (str_contains($line, '.WARNING') || str_contains($line, '.ALERT')) {
                                $levelMatch = true;
                            }
                            break;
                        case 'info':
                            if (str_contains($line, '.INFO') || str_contains($line, '.NOTICE')) {
                                $levelMatch = true;
                            }
                            break;
                        case 'debug':
                            if (str_contains($line, '.DEBUG')) {
                                $levelMatch = true;
                            }
                            break;
                    }
                    if (!$levelMatch) continue;
                }

                $logs[] = $this->parseLogLine($line);

                if (count($logs) >= $filters['lines']) break;
            }
        }

        // Get log file info
        $logInfo = [
            'exists' => file_exists($logFile),
            'size' => file_exists($logFile) ? $this->formatBytes(filesize($logFile)) : '0 B',
            'modified' => file_exists($logFile) ? date('Y-m-d H:i:s', filemtime($logFile)) : 'N/A',
            'lines' => file_exists($logFile) ? count(file($logFile)) : 0,
        ];

        return view('admin.logs.index', compact('logs', 'filters', 'logInfo'));
    }

    public function download(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            return redirect()->back()->with('error', 'Log file not found.');
        }

        $filename = 'laravel-' . date('Y-m-d-H-i-s') . '.log';

        return new StreamedResponse(function () use ($logFile) {
            readfile($logFile);
        }, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function clear(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            File::put($logFile, '');
            return redirect()->back()->with('success', 'Log file cleared successfully.');
        }

        return redirect()->back()->with('error', 'Log file not found.');
    }

    private function parseLogLine(string $line): array
    {
        $parsed = [
            'raw' => $line,
            'timestamp' => null,
            'level' => 'INFO',
            'message' => $line,
            'context' => [],
        ];

        // Try to extract timestamp and level
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](.+?)\.(.+?)\s+(.+)$/', $line, $matches)) {
            $parsed['timestamp'] = $matches[1];
            $parsed['level'] = strtoupper($matches[3]);
            $parsed['message'] = $matches[4];

            // Try to extract JSON context
            if (preg_match('/\{.*\}$/', $parsed['message'], $jsonMatch)) {
                try {
                    $parsed['context'] = json_decode($jsonMatch[0], true) ?: [];
                    $parsed['message'] = str_replace($jsonMatch[0], '', $parsed['message']);
                } catch (\Exception $e) {
                    // Invalid JSON, keep as is
                }
            }
        }

        // Add CSS classes for styling
        $parsed['level_class'] = match($parsed['level']) {
            'ERROR', 'CRITICAL' => 'text-red-600',
            'WARNING', 'ALERT' => 'text-yellow-600',
            'INFO', 'NOTICE' => 'text-blue-600',
            'DEBUG' => 'text-gray-600',
            default => 'text-gray-600',
        };

        return $parsed;
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function tail(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = $request->get('lines', 50);

        if (!file_exists($logFile)) {
            return response()->json(['logs' => []]);
        }

        $content = File::get($logFile);
        $allLines = explode("\n", $content);
        $recentLines = array_slice($allLines, -$lines);

        $logs = [];
        foreach ($recentLines as $line) {
            if (!empty(trim($line))) {
                $logs[] = $this->parseLogLine($line);
            }
        }

        return response()->json(['logs' => $logs]);
    }
}
