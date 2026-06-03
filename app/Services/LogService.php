<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogService
{
    public function getFilteredLogs(array $filters): array
    {
        $logFiles = $this->getLogFilesForDateRange($filters['date_from'], $filters['date_to']);
        $logs = [];

        foreach ($logFiles as $logFile) {
            if (! file_exists($logFile)) {
                continue;
            }

            $content = File::get($logFile);
            $lines = array_reverse(explode("\n", $content));

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                if (! empty($filters['search']) && ! str_contains(strtolower($line), strtolower($filters['search']))) {
                    continue;
                }

                if ($filters['level'] !== 'all' && ! $this->matchesLogLevel($line, $filters['level'])) {
                    continue;
                }

                $logs[] = $this->parseLogLine($line);

                if (count($logs) >= $filters['lines']) {
                    break 2;
                }
            }
        }

        return $logs;
    }

    private function getLogFilesForDateRange(?string $dateFrom, ?string $dateTo): array
    {
        $logFiles = [];
        $logDirectory = storage_path('logs');

        if (! $dateFrom && ! $dateTo) {
            $logFiles[] = storage_path('logs/laravel.log');

            return $logFiles;
        }

        $startDate = $dateFrom ? Carbon::parse($dateFrom) : Carbon::now()->subDays(7);
        $endDate = $dateTo ? Carbon::parse($dateTo) : Carbon::now();

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $logFile = $logDirectory.'/laravel-'.$dateStr.'.log';
            if (file_exists($logFile)) {
                $logFiles[] = $logFile;
            }
            $currentDate->addDay();
        }

        if (empty($logFiles)) {
            $logFiles[] = storage_path('logs/laravel.log');
        }

        return array_reverse($logFiles);
    }

    public function getLogFileInfo(): array
    {
        $logFile = storage_path('logs/laravel.log');

        return [
            'exists' => file_exists($logFile),
            'size' => file_exists($logFile) ? $this->formatBytes(filesize($logFile)) : '0 B',
            'modified' => file_exists($logFile) ? date('Y-m-d H:i:s', filemtime($logFile)) : 'N/A',
            'lines' => file_exists($logFile) ? count(file($logFile)) : 0,
        ];
    }

    public function downloadLogFile(): StreamedResponse
    {
        $logFile = storage_path('logs/laravel.log');
        $filename = 'laravel-'.date('Y-m-d-H-i-s').'.log';

        return new StreamedResponse(function () use ($logFile) {
            readfile($logFile);
        }, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function clearLogFile(): bool
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            File::put($logFile, '');

            return true;
        }

        return false;
    }

    public function getRecentLogs(int $lines = 50): array
    {
        $logFile = storage_path('logs/laravel.log');

        if (! file_exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $allLines = explode("\n", $content);
        $recentLines = array_slice($allLines, -$lines);

        $logs = [];
        foreach ($recentLines as $line) {
            if (! empty(trim($line))) {
                $logs[] = $this->parseLogLine($line);
            }
        }

        return $logs;
    }

    private function matchesLogLevel(string $line, string $level): bool
    {
        return match ($level) {
            'error' => str_contains($line, '.ERROR') || str_contains($line, '.CRITICAL'),
            'warning' => str_contains($line, '.WARNING') || str_contains($line, '.ALERT'),
            'info' => str_contains($line, '.INFO') || str_contains($line, '.NOTICE'),
            'debug' => str_contains($line, '.DEBUG'),
            default => false,
        };
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

        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](.+?)\.(.+?)\s+(.+)$/', $line, $matches)) {
            $parsed['timestamp'] = $matches[1];
            $parsed['level'] = strtoupper($matches[3]);
            $parsed['message'] = $matches[4];

            // Try to extract JSON from the end of the message
            // Handle multi-line JSON by finding the last complete JSON object
            $jsonStart = strrpos($parsed['message'], '{');
            if ($jsonStart !== false) {
                $jsonString = substr($parsed['message'], $jsonStart);
                // Try to decode the JSON
                try {
                    $decoded = json_decode($jsonString, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $parsed['context'] = $decoded;
                        // Keep the full message intact, don't remove JSON
                    }
                } catch (\Exception $e) {
                    // JSON parsing failed, keep the message as is
                }
            }
        }

        $parsed['level_class'] = match ($parsed['level']) {
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

        return round($bytes, $precision).' '.$units[$i];
    }
}
