<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitService
{
    private string $provider;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function checkRateLimit(): bool
    {
        $config = config("ai.providers.{$this->provider}.rate_limit");

        if (!$config || empty($config['requests_per_minute'])) {
            return true; // No rate limiting configured
        }

        $key = "ai_rate_limit:{$this->provider}:" . now()->format('Y-m-d H:i');

        // Check minute limit
        if ($this->exceedsLimit($key, $config['requests_per_minute'], 60)) {
            Log::warning("AI provider rate limit exceeded", [
                'action' => 'ai_rate_limit_exceeded',
                'business_context' => 'ai_code_review',
                'provider' => $this->provider,
                'limit_type' => 'per_minute',
                'limit' => $config['requests_per_minute'],
                'warning_type' => 'rate_limit'
            ]);
            return false;
        }

        // Check hourly limit
        $hourlyKey = "ai_rate_limit:{$this->provider}:" . now()->format('Y-m-d H');
        if ($this->exceedsLimit($hourlyKey, $config['requests_per_hour'], 3600)) {
            Log::warning("AI provider rate limit exceeded", [
                'action' => 'ai_rate_limit_exceeded',
                'business_context' => 'ai_code_review',
                'provider' => $this->provider,
                'limit_type' => 'per_hour',
                'limit' => $config['requests_per_hour'],
                'warning_type' => 'rate_limit'
            ]);
            return false;
        }

        return true;
    }

    public function hitRateLimit(): void
    {
        $config = config("ai.providers.{$this->provider}.rate_limit");

        if (!$config) {
            return;
        }

        // Increment counters
        $minuteKey = "ai_rate_limit:{$this->provider}:" . now()->format('Y-m-d H:i');
        $hourlyKey = "ai_rate_limit:{$this->provider}:" . now()->format('Y-m-d H');

        Cache::increment($minuteKey);
        Cache::increment($hourlyKey);

        // Set expiration for counters
        Cache::put($minuteKey, Cache::get($minuteKey, 0), 60);
        Cache::put($hourlyKey, Cache::get($hourlyKey, 0), 3600);
    }

    public function getRetryAfterSeconds(): int
    {
        $config = config("ai.providers.{$this->provider}.rate_limit");
        return $config['retry_after_seconds'] ?? 60;
    }

    public function getRemainingRequests(): array
    {
        $config = config("ai.providers.{$this->provider}.rate_limit");

        if (!$config) {
            return ['minute' => null, 'hour' => null];
        }

        $minuteKey = "ai_rate_limit:{$this->provider}:" . now()->format('Y-m-d H:i');
        $hourlyKey = "ai_rate_limit:{$this->provider}:" . now()->format('Y-m-d H');

        $minuteCount = Cache::get($minuteKey, 0);
        $hourlyCount = Cache::get($hourlyKey, 0);

        return [
            'minute' => max(0, $config['requests_per_minute'] - $minuteCount),
            'hour' => max(0, $config['requests_per_hour'] - $hourlyCount),
        ];
    }

    private function exceedsLimit(string $key, int $limit, int $ttl): bool
    {
        $current = Cache::get($key, 0);
        return $current >= $limit;
    }

    public function resetRateLimit(): void
    {
        $patterns = [
            "ai_rate_limit:{$this->provider}:*"
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
