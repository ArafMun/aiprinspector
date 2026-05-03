<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\RateLimitService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    private RateLimitService $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new RateLimitService('openai');
    }

    public function review(array $chunk): string
    {
        // Check rate limit before making API call
        if (!$this->rateLimiter->checkRateLimit()) {
            Log::warning('OpenAI API rate limited', [
                'action' => 'openai_rate_limited',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'retry_after' => $this->rateLimiter->getRetryAfterSeconds(),
                'remaining' => $this->rateLimiter->getRemainingRequests(),
                'warning_type' => 'rate_limit'
            ]);
            return 'Rate limit exceeded. Please try again later.';
        }

        // Hit the rate limit counter
        $this->rateLimiter->hitRateLimit();

        try {
            $prompt = $this->buildPrompt($chunk);

            Log::info('OpenAI API request', [
                'action' => 'openai_api_request',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'model' => config('services.openai.model', 'gpt-4'),
                'prompt_length' => strlen($prompt),
                'api_key_set' => !empty(config('services.openai.api_key')),
                'remaining_requests' => $this->rateLimiter->getRemainingRequests()
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4'),
                'max_tokens' => 4000,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a senior code reviewer conducting a thorough pull request review. Provide constructive, actionable feedback.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
            ]);

            Log::info('OpenAI API response', [
                'action' => 'openai_api_response',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'status' => $response->status(),
                'successful' => $response->successful(),
                'response_body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error response', [
                    'action' => 'openai_api_error',
                    'business_context' => 'ai_code_review',
                    'file' => $chunk['file'] ?? 'unknown',
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error_type' => 'api_error'
                ]);
                return ''; // Return empty string on API errors
            }

            $responseData = $response->json();
            $review = $responseData['choices'][0]['message']['content'] ?? '';

            Log::info('OpenAI review generated', [
                'action' => 'openai_review_generated',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'review_length' => strlen($review),
                'review_empty' => empty($review),
                'has_choices' => isset($responseData['choices']) && !empty($responseData['choices'])
            ]);

            return $review;
        } catch (ConnectionException $e) {
            Log::error('OpenAI API connection failed', [
                'action' => 'openai_connection_error',
                'business_context' => 'ai_code_review',
                'error' => $e->getMessage(),
                'file' => $chunk['file'] ?? 'unknown',
                'error_type' => 'connection_error'
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('OpenAI API error', [
                'action' => 'openai_general_error',
                'business_context' => 'ai_code_review',
                'error' => $e->getMessage(),
                'file' => $chunk['file'] ?? 'unknown',
                'error_type' => 'general_error'
            ]);
            return ''; // Return empty string on general errors
        }
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.openai.api_key'));
    }

    private function buildPrompt(array $chunk): string
    {
        $fileContext = $chunk['file'] ?? 'unknown file';
        $patch = $chunk['patch'] ?? '';

        return <<<PROMPT
File: {$fileContext}

Analyze the following code diff and provide constructive feedback:

{$patch}

Focus on:
- Code quality and best practices
- Security vulnerabilities
- Performance issues
- Bug potential
- Code maintainability and readability
- Adherence to coding standards
- Logic errors and edge cases

Format your response as follows:

## 🔍 Review Summary
[Brief summary of changes]

## 🚨 Issues Found
### Severity: (critical/high/medium/low)
**Issue:** [Clear description]
**Why it matters:** [Explanation of impact]
**Suggested fix:** [Specific recommendation]

## 💡 Suggestions
### Severity: (medium/low)
**Suggestion:** [Improvement recommendation]
**Why it helps:** [Benefit explanation]

## ✅ Positive Notes
[Mention good practices or well-written code]

Be specific, constructive, and provide actionable feedback.
PROMPT;
    }
}
