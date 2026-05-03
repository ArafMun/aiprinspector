<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            'current_status' => $this->status
        ]);

        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        Log::info('Review marked as processing', [
            'action' => 'review_processing_confirmed',
            'business_context' => 'ai_code_review',
            'review_id' => $this->id,
            'new_status' => $this->fresh()->status
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
            'processed_chunks' => $processedChunks
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
            'processed_chunks' => $processedChunks
        ]);
    }

    public function markAsPartial(int $processedChunks, string $errorMessage = null): void
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
}
