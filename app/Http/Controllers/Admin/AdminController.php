<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PullRequestReview;
use App\Services\AI\RateLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get overview statistics
        $stats = [
            'total_reviews' => PullRequestReview::count(),
            'completed_reviews' => PullRequestReview::where('status', PullRequestReview::STATUS_COMPLETED)->count(),
            'failed_reviews' => PullRequestReview::where('status', PullRequestReview::STATUS_FAILED)->count(),
            'processing_reviews' => PullRequestReview::where('status', PullRequestReview::STATUS_PROCESSING)->count(),
            'pending_reviews' => PullRequestReview::where('status', PullRequestReview::STATUS_PENDING)->count(),
            'total_users' => \App\Models\User::count(),
            'admin_users' => \App\Models\User::where('is_admin', true)->count(),
            'total_roles' => \App\Models\Role::count(),
            'active_roles' => \App\Models\Role::where('is_active', true)->count(),
        ];

        // Get recent reviews
        $recentReviews = PullRequestReview::with('repo')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get AI provider status
        $claudeRateLimiter = new RateLimitService('claude');
        $openaiRateLimiter = new RateLimitService('openai');

        $aiStatus = [
            'claude' => [
                'remaining_requests' => $claudeRateLimiter->getRemainingRequests(),
                'configured' => !empty(config('services.claude.api_key')),
            ],
            'openai' => [
                'remaining_requests' => $openaiRateLimiter->getRemainingRequests(),
                'configured' => !empty(config('services.openai.api_key')),
            ],
        ];

        // Get daily review trends (last 7 days)
        $dailyTrends = PullRequestReview::select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                \DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get recent users
        $recentUsers = \App\Models\User::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get role distribution
        $roleDistribution = \App\Models\Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentReviews', 'aiStatus', 'dailyTrends', 'recentUsers', 'roleDistribution'));
    }

    public function statistics()
    {
        // Get comprehensive statistics
        $timeRanges = [
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
        ];

        $statistics = [];

        foreach ($timeRanges as $range => $date) {
            $statistics[$range] = [
                'total_reviews' => PullRequestReview::where('created_at', '>=', $date)->count(),
                'completed_reviews' => PullRequestReview::where('created_at', '>=', $date)
                    ->where('status', PullRequestReview::STATUS_COMPLETED)->count(),
                'failed_reviews' => PullRequestReview::where('created_at', '>=', $date)
                    ->where('status', PullRequestReview::STATUS_FAILED)->count(),
                'success_rate' => 0,
                'avg_processing_time' => 0,
            ];

            // Calculate success rate
            if ($statistics[$range]['total_reviews'] > 0) {
                $statistics[$range]['success_rate'] =
                    ($statistics[$range]['completed_reviews'] / $statistics[$range]['total_reviews']) * 100;
            }

            // Calculate average processing time
            $completedReviews = PullRequestReview::where('created_at', '>=', $date)
                ->where('status', PullRequestReview::STATUS_COMPLETED)
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->get();

            if ($completedReviews->count() > 0) {
                $totalTime = $completedReviews->sum(function ($review) {
                    return $review->started_at->diffInSeconds($review->completed_at);
                });
                $statistics[$range]['avg_processing_time'] = $totalTime / $completedReviews->count();
            }
        }

        // Get repository statistics
        $repoStats = PullRequestReview::select('repo_name')
            ->selectRaw('COUNT(*) as total_reviews')
            ->selectRaw('SUM(CASE WHEN status = ' . PullRequestReview::STATUS_COMPLETED . ' THEN 1 ELSE 0 END) as completed_reviews')
            ->selectRaw('AVG(CASE WHEN started_at IS NOT NULL AND completed_at IS NOT NULL
                THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) END) as avg_processing_time')
            ->groupBy('repo_name')
            ->orderBy('total_reviews', 'desc')
            ->limit(10)
            ->get();

        // Get error analysis
        $errorAnalysis = PullRequestReview::where('status', PullRequestReview::STATUS_FAILED)
            ->whereNotNull('error_message')
            ->select('error_message')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('error_message')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.statistics', compact('statistics', 'repoStats', 'errorAnalysis'));
    }

    public function statisticsApi(Request $request)
    {
        $range = $request->get('range', 'week');
        $date = match($range) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek(),
        };

        // Get hourly data for charts
        $hourlyData = PullRequestReview::select(
                \DB::raw('HOUR(created_at) as hour'),
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN status = "2" THEN 1 ELSE 0 END) as completed'),
                \DB::raw('SUM(CASE WHEN status = "4" THEN 1 ELSE 0 END) as failed')
            )
            ->where('created_at', '>=', $date)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Get provider usage
        $providerUsage = [
            'claude' => [
                'requests' => Cache::get('ai_requests_claude', 0),
                'success_rate' => Cache::get('ai_success_claude', 0),
            ],
            'openai' => [
                'requests' => Cache::get('ai_requests_openai', 0),
                'success_rate' => Cache::get('ai_success_openai', 0),
            ],
        ];

        return response()->json([
            'hourly_data' => $hourlyData,
            'provider_usage' => $providerUsage,
            'range' => $range,
        ]);
    }
}
