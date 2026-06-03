<?php

namespace App\Services;

use App\Jobs\ProcessPullRequestJob;
use App\Models\PullRequestReview;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function getFilteredReviews(array $filters): LengthAwarePaginator
    {
        return PullRequestReview::getFilteredWithRepo($filters);
    }

    public function getReviewStatistics(): array
    {
        return [
            'statuses' => PullRequestReview::getStatusStatistics(),
            'repositories' => PullRequestReview::getDistinctRepositories(),
        ];
    }

    public function getReviewWithDetails(PullRequestReview $review): array
    {
        $review->load(['repo']);
        $logs = $this->getReviewLogs($review);
        $aiProvider = $this->getAIProviderForReview($review);

        return [
            'review' => $review,
            'logs' => $logs,
            'aiProvider' => $aiProvider,
        ];
    }

    public function retryReview(PullRequestReview $review): bool
    {
        if ($review->status !== PullRequestReview::STATUS_FAILED) {
            return false;
        }

        $review->update([
            'status' => PullRequestReview::STATUS_PENDING,
            'error_message' => null,
            'processed_chunks' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);

        ProcessPullRequestJob::dispatch($review->payload, $review);

        return true;
    }

    public function deleteReview(PullRequestReview $review): bool
    {
        if ($review->status !== PullRequestReview::STATUS_FAILED ||
            $review->created_at->gt(now()->subDays(30))) {
            return false;
        }

        $review->delete();

        return true;
    }

    private function getReviewLogs(PullRequestReview $review): array
    {
        $logs = [];
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (str_contains($line, "review_id\":{$review->id}") ||
                    str_contains($line, "pr_number\":{$review->pr_number}") ||
                    str_contains($line, "repo\":\"{$review->repo_name}\"")) {
                    $logs[] = $line;
                }
            }
        }

        return array_reverse(array_slice($logs, -50));
    }

    private function getAIProviderForReview(PullRequestReview $review): ?string
    {
        $logs = $this->getReviewLogs($review);

        foreach ($logs as $log) {
            if (str_contains($log, 'Claude API')) {
                return 'claude';
            } elseif (str_contains($log, 'OpenAI API')) {
                return 'openai';
            }
        }

        return null;
    }
}
