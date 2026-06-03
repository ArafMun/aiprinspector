<?php

namespace App\Services;

use App\Models\PullRequestReview;
use App\Models\Role;
use App\Models\User;
use App\Services\AI\RateLimitService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminService
{
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getOverviewStats(),
            'recentReviews' => $this->getRecentReviews(),
            'aiStatus' => $this->getAIProviderStatus(),
            'dailyTrends' => $this->getDailyTrends(),
            'recentUsers' => $this->getRecentUsers(),
            'roleDistribution' => $this->getRoleDistribution(),
        ];
    }

    public function getStatisticsData(): array
    {
        return [
            'statistics' => $this->getStatisticsByTimeRange(),
            'repoStats' => $this->getRepositoryStats(),
            'errorAnalysis' => $this->getErrorAnalysis(),
        ];
    }

    public function getStatisticsApiData(string $range): array
    {
        $date = $this->getDateFromRange($range);

        return [
            'hourly_data' => $this->getHourlyData($date),
            'provider_usage' => $this->getProviderUsage($date),
            'range' => $range,
        ];
    }

    private function getOverviewStats(): array
    {
        return [
            'total_reviews' => PullRequestReview::getTotalCount(),
            'completed_reviews' => PullRequestReview::getCountByStatus(PullRequestReview::STATUS_COMPLETED),
            'failed_reviews' => PullRequestReview::getCountByStatus(PullRequestReview::STATUS_FAILED),
            'processing_reviews' => PullRequestReview::getCountByStatus(PullRequestReview::STATUS_PROCESSING),
            'pending_reviews' => PullRequestReview::getCountByStatus(PullRequestReview::STATUS_PENDING),
            'total_users' => User::getTotalCount(),
            'admin_users' => User::getAdminCount(),
            'total_roles' => Role::getTotalCount(),
            'active_roles' => Role::getActiveCount(),
        ];
    }

    private function getRecentReviews(): Collection
    {
        return PullRequestReview::getRecent(10);
    }

    private function getAIProviderStatus(): array
    {
        $claudeRateLimiter = new RateLimitService('claude');
        $openaiRateLimiter = new RateLimitService('openai');

        return [
            'claude' => [
                'remaining_requests' => $claudeRateLimiter->getRemainingRequests(),
                'configured' => ! empty(config('services.claude.api_key')),
            ],
            'openai' => [
                'remaining_requests' => $openaiRateLimiter->getRemainingRequests(),
                'configured' => ! empty(config('services.openai.api_key')),
            ],
        ];
    }

    private function getDailyTrends(): Collection
    {
        return PullRequestReview::getDailyTrends(7);
    }

    private function getRecentUsers(): Collection
    {
        return User::getRecent(5);
    }

    private function getRoleDistribution(): Collection
    {
        return Role::getRoleDistribution();
    }

    private function getStatisticsByTimeRange(): array
    {
        $timeRanges = [
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
        ];

        $statistics = [];

        foreach ($timeRanges as $range => $date) {
            $statistics[$range] = [
                'total_reviews' => PullRequestReview::getCountByDateRange($date),
                'completed_reviews' => PullRequestReview::getCountByDateRange($date, PullRequestReview::STATUS_COMPLETED),
                'failed_reviews' => PullRequestReview::getCountByDateRange($date, PullRequestReview::STATUS_FAILED),
                'success_rate' => 0,
                'avg_processing_time' => 0,
            ];

            if ($statistics[$range]['total_reviews'] > 0) {
                $statistics[$range]['success_rate'] =
                    ($statistics[$range]['completed_reviews'] / $statistics[$range]['total_reviews']) * 100;
            }

            $completedReviews = PullRequestReview::getCompletedWithTiming($date);

            if ($completedReviews->count() > 0) {
                $totalTime = $completedReviews->sum(function ($review) {
                    return $review->started_at->diffInSeconds($review->completed_at);
                });
                $statistics[$range]['avg_processing_time'] = $totalTime / $completedReviews->count();
            }
        }

        return $statistics;
    }

    private function getRepositoryStats(): Collection
    {
        return PullRequestReview::getRepositoryStats(10);
    }

    private function getErrorAnalysis(): Collection
    {
        return PullRequestReview::getErrorAnalysis(5);
    }

    private function getDateFromRange(string $range): Carbon
    {
        return match ($range) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek(),
        };
    }

    private function getHourlyData(Carbon $date): Collection
    {
        return PullRequestReview::getHourlyData($date);
    }

    private function getProviderUsage(Carbon $date): array
    {
        $providerUsage = [
            'claude' => [
                'requests' => PullRequestReview::getProviderUsage($date, 'claude'),
                'success_rate' => 0,
            ],
            'openai' => [
                'requests' => PullRequestReview::getProviderUsage($date, 'openai'),
                'success_rate' => 0,
            ],
        ];

        foreach ($providerUsage as $provider => $data) {
            $totalRequests = $data['requests'];
            if ($totalRequests > 0) {
                $completedRequests = PullRequestReview::getProviderCompletedCount($date, $provider);
                $providerUsage[$provider]['success_rate'] = round(($completedRequests / $totalRequests) * 100, 1);
            }
        }

        return $providerUsage;
    }
}
