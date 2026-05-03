<?php

namespace App\Services;

use App\Services\AI\AIProviderFactory;
use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Log;

class AIService
{
    private AIProviderInterface $provider;

    public function __construct(?string $provider = null)
    {
        $this->provider = AIProviderFactory::create($provider);
    }

    public function review(array $chunk): string
    {
        try {
            return $this->provider->review($chunk);
        } catch (\Exception $e) {
            Log::error('AI service error', [
                'action' => 'ai_service_error',
                'business_context' => 'ai_code_review',
                'provider' => $this->provider->getName(),
                'error' => $e->getMessage(),
                'file' => $chunk['file'] ?? 'unknown',
                'error_type' => 'service_error'
            ]);

            // Try fallback if enabled
            if (config('ai.fallback_enabled')) {
                return $this->tryFallback($chunk);
            }

            return ''; // Return empty string on service errors
        }
    }

    public function getProviderName(): string
    {
        return $this->provider->getName();
    }

    private function tryFallback(array $chunk): string
    {
        $fallbackProvider = config('ai.fallback_provider');

        try {
            Log::info('Attempting fallback provider', [
                'action' => 'ai_fallback_attempt',
                'business_context' => 'ai_code_review',
                'fallback' => $fallbackProvider,
                'file' => $chunk['file'] ?? 'unknown',
                'primary_provider' => $this->provider->getName()
            ]);

            $fallback = AIProviderFactory::create($fallbackProvider);
            return $fallback->review($chunk);
        } catch (\Exception $e) {
            Log::error('Fallback provider also failed', [
                'action' => 'ai_fallback_failed',
                'business_context' => 'ai_code_review',
                'fallback' => $fallbackProvider,
                'error' => $e->getMessage(),
                'file' => $chunk['file'] ?? 'unknown',
                'primary_provider' => $this->provider->getName(),
                'error_type' => 'fallback_error'
            ]);

            return ''; // Return empty string on fallback errors
        }
    }
}
