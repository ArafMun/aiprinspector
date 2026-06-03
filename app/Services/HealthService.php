<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PullRequestReview;
use App\Models\User;

class HealthService
{
    public function getHealthStatus(): array
    {
        $startTime = microtime(true);

        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => app()->version(),
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
                'storage' => $this->checkStorage(),
                'ai_providers' => [
                    'claude' => $this->checkClaudeConfig(),
                    'openai' => $this->checkOpenAIConfig(),
                ],
                'gitea' => $this->checkGiteaConfig(),
            ],
            'metrics' => [
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'active_users' => User::getActiveCount(30),
                'recent_reviews' => PullRequestReview::getRecentCount(24),
                'error_rate' => $this->getErrorRate(),
            ],
        ];

        $isHealthy = $health['services']['database']['status'] === 'healthy' &&
                    $health['services']['cache']['status'] === 'healthy' &&
                    $health['services']['storage']['status'] === 'healthy';

        $health['status'] = $isHealthy ? 'healthy' : 'unhealthy';

        return $health;
    }

    public function getDetailedHealthStatus(): array
    {
        $startTime = microtime(true);

        return [
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'timezone' => config('app.timezone'),
                'debug_mode' => config('app.debug'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'database' => array_merge($this->checkDatabase(), [
                'connection' => config('database.default'),
                'driver' => config('database.connections.'.config('database.default').'.driver'),
                'host' => config('database.connections.'.config('database.default').'.host'),
                'database' => config('database.connections.'.config('database.default').'.database'),
            ]),
            'cache' => array_merge($this->checkCache(), [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ]),
            'performance' => [
                'memory_usage' => $this->getMemoryUsage(),
                'uptime' => $this->getUptime(),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ],
            'metrics' => [
                'active_users' => User::getActiveCount(30),
                'total_users' => User::getTotalCount(),
                'recent_reviews' => PullRequestReview::getRecentCount(24),
                'total_reviews' => PullRequestReview::getTotalCount(),
                'error_rate' => $this->getErrorRate(),
                'audit_logs_today' => AuditLog::getCountByDate(today()->toDateString()),
            ],
        ];
    }

    private function checkClaudeConfig(): array
    {
        $apiKey = config('services.claude.api_key');
        $model = config('services.claude.model');

        return [
            'status' => $apiKey ? 'healthy' : 'misconfigured',
            'model' => $model,
            'api_key_configured' => ! empty($apiKey),
        ];
    }

    private function checkGiteaConfig(): array
    {
        $baseUrl = config('services.gitea.base');
        $token = config('services.gitea.token');
        $webhookSecret = config('services.gitea.webhook_secret');

        return [
            'status' => ($baseUrl && $token && $webhookSecret) ? 'healthy' : 'misconfigured',
            'base_url_configured' => ! empty($baseUrl),
            'token_configured' => ! empty($token),
            'webhook_secret_configured' => ! empty($webhookSecret),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();

            return ['status' => 'healthy'];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            \Queue::connection();

            return ['status' => 'healthy'];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            \Cache::put('health_check_test', 'test', 60);
            $value = \Cache::get('health_check_test');
            \Cache::forget('health_check_test');

            return [
                'status' => $value === 'test' ? 'healthy' : 'unhealthy',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = storage_path('app/health_check_test.txt');
            file_put_contents($testFile, 'test');
            $read = file_get_contents($testFile);
            unlink($testFile);

            return [
                'status' => $read === 'test' ? 'healthy' : 'unhealthy',
                'disk_space_free' => $this->getDiskSpace(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkOpenAIConfig(): array
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model');

        return [
            'status' => $apiKey ? 'healthy' : 'misconfigured',
            'model' => $model,
            'api_key_configured' => ! empty($apiKey),
        ];
    }

    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));

        return [
            'current' => $this->formatBytes($memoryUsage),
            'current_bytes' => $memoryUsage,
            'limit' => $this->formatBytes($memoryLimit),
            'limit_bytes' => $memoryLimit,
            'percentage' => round(($memoryUsage / $memoryLimit) * 100, 2),
        ];
    }

    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            return sprintf('Load: %.2f, %.2f, %.2f', $load[0], $load[1], $load[2]);
        }

        return 'N/A';
    }

    private function getDiskSpace(): array
    {
        $total = disk_total_space(storage_path());
        $free = disk_free_space(storage_path());
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($used),
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    private function getErrorRate(): float
    {
        try {
            $totalReviews = PullRequestReview::getRecentCount(24);
            $failedReviews = PullRequestReview::getFailedCount(24);

            return $totalReviews > 0 ? round(($failedReviews / $totalReviews) * 100, 2) : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function parseMemoryLimit(string $limit): int
    {
        $limit = strtoupper($limit);
        $multiplier = 1;

        if (str_ends_with($limit, 'G')) {
            $multiplier = 1024 * 1024 * 1024;
            $limit = substr($limit, 0, -1);
        } elseif (str_ends_with($limit, 'M')) {
            $multiplier = 1024 * 1024;
            $limit = substr($limit, 0, -1);
        } elseif (str_ends_with($limit, 'K')) {
            $multiplier = 1024;
            $limit = substr($limit, 0, -1);
        }

        return (int) $limit * $multiplier;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
