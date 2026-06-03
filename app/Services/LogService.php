<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogService
{
    public function getFilteredLogs(array $filters): array
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logFile)) {
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
                    break;
                }
            }
        }

        return $logs;
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

            if (preg_match('/\{.*\}$/', $parsed['message'], $jsonMatch)) {
                try {
                    $parsed['context'] = json_decode($jsonMatch[0], true) ?: [];
                    $parsed['message'] = str_replace($jsonMatch[0], '', $parsed['message']);
                } catch (\Exception $e) {
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
