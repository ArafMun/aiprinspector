<?php

namespace App\Services;

use App\Jobs\ProcessPullRequestJob;
use App\Models\PullRequestReview;
use App\Models\Repository;
use Illuminate\Http\Request;

class WebhookService
{
    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();

        if (! $this->verifyWebhookSignature($request)) {
            return ['success' => false, 'error' => 'Invalid signature', 'status' => 401];
        }

        if ($request->header('X-Gitea-Event') !== 'pull_request') {
            return ['success' => true, 'ignored' => true];
        }

        $action = $request->input('action');
        if (! in_array($action, ['opened', 'synchronize'])) {
            return ['success' => true, 'ignored' => true];
        }

        if (! $this->validatePayload($payload)) {
            return ['success' => false, 'error' => 'Invalid payload', 'status' => 400];
        }

        $repository = Repository::findOrCreateByName(
            $payload['repository']['full_name'],
            [
                'url' => $payload['repository']['html_url'] ?? null,
                'description' => $payload['repository']['description'] ?? null,
                'default_branch' => $payload['repository']['default_branch'] ?? 'main',
            ]
        );

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

        \Log::info('Pull request webhook queued', [
            'action' => 'webhook_pr_queued',
            'business_context' => 'ai_code_review',
            'repo' => $payload['repository']['full_name'] ?? 'unknown',
            'pr_number' => $payload['number'] ?? 'unknown',
            'pr_action' => $action,
            'review_id' => $review->id,
        ]);

        return ['success' => true, 'queued' => true];
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
