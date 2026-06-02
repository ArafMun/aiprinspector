<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPullRequestJob;
use App\Models\PullRequestReview;
use App\Models\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature
            if (! $this->verifyWebhookSignature($request)) {
                Log::warning('Invalid webhook signature received', [
                    'action' => 'webhook_invalid_signature',
                    'business_context' => 'ai_code_review',
                    'ip' => $request->ip(),
                    'event' => $request->header('X-Gitea-Event'),
                    'warning_type' => 'security',
                ]);

                return response()->json(['error' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
            }

            // Check for Gitea pull request event
            if ($request->header('X-Gitea-Event') !== 'pull_request') {
                return response()->json(['ignored' => true]);
            }

            $action = $request->input('action');
            if (! in_array($action, ['opened', 'synchronize'])) {
                return response()->json(['ignored' => true]);
            }

            // Validate payload structure
            $payload = $request->all();
            if (! $this->validatePayload($payload)) {
                Log::error('Invalid webhook payload structure', [
                    'action' => 'webhook_invalid_payload',
                    'business_context' => 'ai_code_review',
                    'error_type' => 'validation_error',
                ]);

                return response()->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
            }

            // Find or create repository
            $repository = Repository::findOrCreateByName(
                $payload['repository']['full_name'],
                [
                    'url' => $payload['repository']['html_url'] ?? null,
                    'description' => $payload['repository']['description'] ?? null,
                    'default_branch' => $payload['repository']['default_branch'] ?? 'main',
                ]
            );

            // Create review record and dispatch job for processing
            $review = PullRequestReview::create([
                'repo_name' => $payload['repository']['full_name'],
                'pr_number' => $payload['number'],
                'commit_sha' => $payload['head']['sha'] ?? null,
                'action' => $payload['action'],
                'payload' => $payload,
                'ai_provider' => config('ai.default_provider', 'claude'),
                'status' => PullRequestReview::STATUS_PENDING,
            ]);

            dispatch(new ProcessPullRequestJob($payload, $review));

            Log::info('Pull request webhook queued', [
                'action' => 'webhook_pr_queued',
                'business_context' => 'ai_code_review',
                'repo' => $payload['repository']['full_name'] ?? 'unknown',
                'pr_number' => $payload['number'] ?? 'unknown',
                'pr_action' => $action,
                'review_id' => $review->id,
            ]);

            return response()->json(['queued' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'action' => 'webhook_processing_failed',
                'business_context' => 'ai_code_review',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'error_type' => 'processing_error',
            ]);

            return response()->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Gitea-Signature');
        $secret = config('services.gitea.webhook_secret');

        if (! $signature || ! $secret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expectedSignature, $signature);
    }

    private function validatePayload(array $payload): bool
    {
        return isset($payload['repository']['full_name']) &&
               isset($payload['number']) &&
               isset($payload['action']);
    }
}
