<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\RateLimitService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    private RateLimitService $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new RateLimitService('gemini');
    }

    public function review(array $chunk): string
    {
        // Check rate limit before making API call
        if (! $this->rateLimiter->checkRateLimit()) {
            Log::warning('Gemini API rate limited', [
                'action' => 'gemini_rate_limited',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'retry_after' => $this->rateLimiter->getRetryAfterSeconds(),
                'remaining' => $this->rateLimiter->getRemainingRequests(),
                'warning_type' => 'rate_limit',
            ]);

            return 'Rate limit exceeded. Please try again later.';
        }

        // Hit the rate limit counter
        $this->rateLimiter->hitRateLimit();

        try {
            $prompt = $this->buildPrompt($chunk);

            Log::info('Gemini API request', [
                'action' => 'gemini_api_request',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'model' => config('services.gemini.model', 'gemini-1.5-pro'),
                'prompt_length' => strlen($prompt),
                'api_key_set' => ! empty(config('services.gemini.api_key')),
                'remaining_requests' => $this->rateLimiter->getRemainingRequests(),
            ]);

            $apiKey = config('services.gemini.api_key');
            $model = config('services.gemini.model', 'gemini-1.5-pro');
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 4000,
                    'temperature' => 0.7,
                ],
            ]);

            Log::info('Gemini API response', [
                'action' => 'gemini_api_response',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'status' => $response->status(),
                'successful' => $response->successful(),
                'response_body' => $response->body(),
            ]);

            if (! $response->successful()) {
                Log::error('Gemini API error response', [
                    'action' => 'gemini_api_error',
                    'business_context' => 'ai_code_review',
                    'file' => $chunk['file'] ?? 'unknown',
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error_type' => 'api_error',
                ]);

                return ''; // Return empty string on API errors
            }

            $responseData = $response->json();
            $review = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

            Log::info('Gemini review generated', [
                'action' => 'gemini_review_generated',
                'business_context' => 'ai_code_review',
                'file' => $chunk['file'] ?? 'unknown',
                'review_length' => strlen($review),
                'review_empty' => empty($review),
                'has_candidates' => isset($responseData['candidates']) && ! empty($responseData['candidates']),
            ]);

            return $review;
        } catch (ConnectionException $e) {
            Log::error('Gemini API connection failed', [
                'action' => 'gemini_connection_error',
                'business_context' => 'ai_code_review',
                'error' => $e->getMessage(),
                'file' => $chunk['file'] ?? 'unknown',
                'error_type' => 'connection_error',
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'action' => 'gemini_general_error',
                'business_context' => 'ai_code_review',
                'error' => $e->getMessage(),
                'file' => $chunk['file'] ?? 'unknown',
                'error_type' => 'general_error',
            ]);

            return ''; // Return empty string on general errors
        }
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.gemini.api_key'));
    }

    private function buildPrompt(array $chunk): string
    {
        $fileContext = $chunk['file'] ?? 'unknown file';
        $patch = $chunk['patch'] ?? '';

        return <<<PROMPT
You are a code reviewer focusing on basic code quality checks.

File: {$fileContext}

Review ONLY the new changes in this diff:

{$patch}

Focus on basic level checks:
- Type errors and type mismatches
- Naming conventions (variables, functions, classes)
- Code style and formatting consistency
- Basic best practices for the language
- Simple syntax errors
- Missing imports or dependencies
- Obvious logic errors

IMPORTANT: For each issue found, you MUST specify the exact line number from the diff.

Format your response as follows:

## Issues Found

Line [LINE_NUMBER]: [ISSUE_TYPE]
- **Problem:** [Clear description of the issue]
- **Suggestion:** [Specific fix]

Repeat for each issue found. Only report actual issues found in the new changes.

If no issues are found, respond with: "No issues found in this change."
PROMPT;
    }
}
