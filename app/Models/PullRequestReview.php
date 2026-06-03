<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PullRequestReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'repo_name',
        'pr_number',
        'commit_sha',
        'action',
        'payload',
        'ai_provider',
        'status',
        'files_count',
        'chunks_count',
        'processed_chunks',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => 'int',
    ];

    public const STATUS_PENDING = 0;

    public const STATUS_PROCESSING = 1;

    public const STATUS_COMPLETED = 2;

    public const STATUS_PARTIAL = 3;

    public const STATUS_FAILED = 4;

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'pending',
            self::STATUS_PROCESSING => 'processing',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_PARTIAL => 'partial',
            self::STATUS_FAILED => 'failed',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->status] ?? 'unknown';
    }

    public function markAsProcessing(): void
    {
        Log::info('Marking review as processing', [
            'action' => 'review_marked_processing',
            'business_context' => 'ai_code_review',
            'review_id' => $this->id,
            'repo_name' => $this->repo_name,
            'pr_number' => $this->pr_number,
            'current_status' => $this->status,
        ]);

        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        Log::info('Review marked as processing', [
            'action' => 'review_processing_confirmed',
            'business_context' => 'ai_code_review',
            'review_id' => $this->id,
            'new_status' => $this->fresh()->status,
        ]);
    }

    public function markAsCompleted(int $processedChunks): void
    {
        Log::info('Marking review as completed', [
            'action' => 'review_marked_completed',
            'business_context' => 'ai_code_review',
            'review_id' => $this->id,
            'repo_name' => $this->repo_name,
            'pr_number' => $this->pr_number,
            'current_status' => $this->status,
            'processed_chunks' => $processedChunks,
        ]);

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_chunks' => $processedChunks,
            'completed_at' => now(),
        ]);

        Log::info('Review marked as completed', [
            'action' => 'review_completion_confirmed',
            'business_context' => 'ai_code_review',
            'review_id' => $this->id,
            'new_status' => $this->fresh()->status,
            'processed_chunks' => $processedChunks,
        ]);
    }

    public function markAsPartial(int $processedChunks, ?string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_PARTIAL,
            'processed_chunks' => $processedChunks,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    public function scopeForRepo($query, string $repoName)
    {
        return $query->where('repo_name', $repoName);
    }

    public function scopeForPr($query, int $prNumber)
    {
        return $query->where('pr_number', $prNumber);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the repository that owns this pull request review.
     */
    public function repo(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'repo_name', 'full_name');
    }

    public static function getFilteredWithRepo(array $filters): LengthAwarePaginator
    {
        $query = self::with(['repo']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['repo'])) {
            $query->where('repo_name', 'like', '%'.$filters['repo'].'%');
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['pr_number'])) {
            $query->where('pr_number', $filters['pr_number']);
        }

        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate(25)->withQueryString();
    }

    public static function getStatusStatistics(): Collection
    {
        return self::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    public static function getDistinctRepositories(): Collection
    {
        return self::select('repo_name')
            ->distinct()
            ->orderBy('repo_name')
            ->pluck('repo_name');
    }

    public static function getRecentCount(int $hours): int
    {
        try {
            return self::where('created_at', '>=', now()->subHours($hours))->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getFailedCount(int $hours): int
    {
        try {
            return self::where('created_at', '>=', now()->subHours($hours))
                ->where('status', self::STATUS_FAILED)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getTotalCount(): int
    {
        try {
            return self::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getCountByStatus(string $status): int
    {
        try {
            return self::where('status', $status)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getRecent(int $limit = 10): Collection
    {
        return self::with('repo')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getDailyTrends(int $days = 7): Collection
    {
        $dailyTrendsData = self::select(
            \DB::raw('DATE(created_at) as date'),
            \DB::raw('COUNT(*) as total'),
            \DB::raw('SUM(CASE WHEN status = '.self::STATUS_COMPLETED.' THEN 1 ELSE 0 END) as completed'),
            \DB::raw('SUM(CASE WHEN status = '.self::STATUS_FAILED.' THEN 1 ELSE 0 END) as failed')
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyTrends = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyTrends->push([
                'date' => $date,
                'total' => $dailyTrendsData->get($date)->total ?? 0,
                'completed' => $dailyTrendsData->get($date)->completed ?? 0,
                'failed' => $dailyTrendsData->get($date)->failed ?? 0,
            ]);
        }

        return $dailyTrends;
    }

    public static function getCountByDateRange(Carbon $date, ?string $status = null): int
    {
        try {
            $query = self::where('created_at', '>=', $date);
            if ($status !== null) {
                $query->where('status', $status);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getCompletedWithTiming(Carbon $date): Collection
    {
        return self::where('created_at', '>=', $date)
            ->where('status', self::STATUS_COMPLETED)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();
    }

    public static function getRepositoryStats(int $limit = 10): Collection
    {
        return self::select('repo_name')
            ->selectRaw('COUNT(*) as total_reviews')
            ->selectRaw('SUM(CASE WHEN status = '.self::STATUS_COMPLETED.' THEN 1 ELSE 0 END) as completed_reviews')
            ->selectRaw('AVG(CASE WHEN started_at IS NOT NULL AND completed_at IS NOT NULL
                THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) END) as avg_processing_time')
            ->groupBy('repo_name')
            ->orderBy('total_reviews', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getErrorAnalysis(int $limit = 5): Collection
    {
        return self::where('status', self::STATUS_FAILED)
            ->whereNotNull('error_message')
            ->select('error_message')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('error_message')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getHourlyData(Carbon $date): Collection
    {
        return self::select(
            \DB::raw('HOUR(created_at) as hour'),
            \DB::raw('COUNT(*) as total'),
            \DB::raw('SUM(CASE WHEN status = '.self::STATUS_COMPLETED.' THEN 1 ELSE 0 END) as completed'),
            \DB::raw('SUM(CASE WHEN status = '.self::STATUS_FAILED.' THEN 1 ELSE 0 END) as failed')
        )
            ->where('created_at', '>=', $date)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    public static function getProviderUsage(Carbon $date, string $provider): int
    {
        try {
            return self::where('ai_provider', $provider)
                ->where('created_at', '>=', $date)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getProviderCompletedCount(Carbon $date, string $provider): int
    {
        try {
            return self::where('ai_provider', $provider)
                ->where('created_at', '>=', $date)
                ->where('status', self::STATUS_COMPLETED)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
