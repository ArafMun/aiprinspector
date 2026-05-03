<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Log;

class AIProviderFactory
{
    private static array $providers = [
        'claude' => ClaudeProvider::class,
        'openai' => OpenAIProvider::class,
    ];

    public static function create(?string $provider = null): AIProviderInterface
    {
        $providerName = $provider ?: config('ai.default_provider', 'claude');

        if (!isset(self::$providers[$providerName])) {
            Log::error("Unknown AI provider: {$providerName}");
            throw new \InvalidArgumentException("Unknown AI provider: {$providerName}");
        }

        $providerClass = self::$providers[$providerName];
        $provider = new $providerClass();

        if (!$provider->isConfigured()) {
            Log::error("AI provider not configured: {$providerName}");
            throw new \RuntimeException("AI provider '{$providerName}' is not configured properly");
        }

        Log::info("Using AI provider: {$providerName}");
        return $provider;
    }

    public static function getAvailableProviders(): array
    {
        return array_keys(self::$providers);
    }

    public static function getConfiguredProviders(): array
    {
        $configured = [];

        foreach (self::$providers as $name => $class) {
            $provider = new $class();
            if ($provider->isConfigured()) {
                $configured[] = $name;
            }
        }

        return $configured;
    }

    public static function registerProvider(string $name, string $class): void
    {
        if (!is_subclass_of($class, AIProviderInterface::class)) {
            throw new \InvalidArgumentException("Provider class must implement AIProviderInterface");
        }

        self::$providers[$name] = $class;
    }
}
