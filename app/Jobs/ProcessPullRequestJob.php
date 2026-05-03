<?php

namespace App\Jobs;

use App\Models\PullRequestReview;
use App\Services\AIService;
use App\Services\ChunkService;
use App\Services\CommentService;
use App\Services\GiteaService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPullRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    public int $tries = 3;
    public int $timeout = 300;
    public string $queue = 'ai-review';

    public function __construct(public array $payload, private ?PullRequestReview $review = null)
    {
        // Create or find the review record
        $this->review = $this->review ?: $this->createReviewRecord();
    }

    /**
     * @throws ConnectionException|Throwable
     */
    public function handle(): void
    {
        try {
            $repo = $this->payload['repository']['full_name'];
            $prNumber = $this->payload['number'];
            $action = $this->payload['action'];
            $commitSha = $this->payload['head']['sha'] ?? null;

            // Mark as processing
            $this->review->markAsProcessing();

            Log::info('Processing pull request', [
                'action' => 'pr_processing_started',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $prNumber,
                'pr_action' => $action,
                'review_id' => $this->review->id,
                'commit_sha' => $commitSha
            ]);

            // Get pull request files
            $diffs = app(GiteaService::class)->getPullRequestFiles($repo, $prNumber);

            if (empty($diffs)) {
                Log::info('No files changed in pull request', [
                    'action' => 'pr_processing_completed_no_files',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $prNumber,
                    'review_id' => $this->review->id,
                    'processed_chunks' => 0
                ]);
                $this->review->markAsCompleted(0);
                return;
            }

            Log::info('Retrieved pull request files', [
                'action' => 'pr_files_retrieved',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $prNumber,
                'file_count' => count($diffs),
                'review_id' => $this->review->id
            ]);

            // Chunk the diffs for processing
            $chunks = app(ChunkService::class)->chunk($diffs);

            // Update review record with file counts
            $this->review->update([
                'files_count' => count($diffs),
                'chunks_count' => count($chunks),
            ]);

            Log::info('Created chunks for processing', [
                'action' => 'pr_chunks_created',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $prNumber,
                'chunk_count' => count($chunks),
                'review_id' => $this->review->id,
                'files_count' => count($diffs)
            ]);

            // Process each chunk
            $processedCount = 0;
            $failedCount = 0;
            $chunkErrors = [];

            foreach ($chunks as $index => $chunk) {
                try {
                    $review = app(AIService::class)->review($chunk);

                    if (!empty($review)) {
                        app(CommentService::class)->post($repo, $prNumber, $review);
                        $processedCount++;

                        Log::info('Posted review comment', [
                            'action' => 'pr_review_comment_posted',
                            'business_context' => 'ai_code_review',
                            'repo' => $repo,
                            'pr_number' => $prNumber,
                            'chunk_index' => $index,
                            'file' => $chunk['file'] ?? 'unknown',
                            'review_id' => $this->review->id,
                            'processed_chunks_count' => $processedCount
                        ]);
                    } else {
                        Log::warning('Empty review generated', [
                            'action' => 'pr_review_empty_generated',
                            'business_context' => 'ai_code_review',
                            'repo' => $repo,
                            'pr_number' => $prNumber,
                            'chunk_index' => $index,
                            'file' => $chunk['file'] ?? 'unknown',
                            'review_id' => $this->review->id,
                            'warning_type' => 'empty_ai_response'
                        ]);
                    }

                    // Add delay to avoid rate limiting
                    if ($index < count($chunks) - 1) {
                        usleep(500000); // 0.5 second delay
                    }

                } catch (Throwable $chunkError) {
                    $failedCount++;
                    $fileName = $chunk['file'] ?? 'unknown';
                    $errorMessage = $chunkError->getMessage();
                    $chunkErrors[] = "Chunk {$index} ({$fileName}): {$errorMessage}";

                    Log::error('Failed to process chunk', [
                        'action' => 'pr_chunk_processing_failed',
                        'business_context' => 'ai_code_review',
                        'repo' => $repo,
                        'pr_number' => $prNumber,
                        'chunk_index' => $index,
                        'file' => $chunk['file'] ?? 'unknown',
                        'error' => $chunkError->getMessage(),
                        'review_id' => $this->review->id,
                        'failed_chunks_count' => $failedCount,
                        'error_type' => 'chunk_processing_error'
                    ]);
                    // Continue processing other chunks
                    continue;
                }
            }

            Log::info('Pull request processing completed', [
                'action' => 'pr_processing_completed',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $prNumber,
                'processed_chunks' => $processedCount,
                'failed_chunks' => $failedCount,
                'total_chunks' => count($chunks),
                'review_id' => $this->review->id,
                'success_rate' => $processedCount / count($chunks)
            ]);

            // Determine final status based on processing results
            Log::info('Determining final status', [
                'action' => 'pr_final_status_determining',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $prNumber,
                'processed_chunks' => $processedCount,
                'failed_chunks' => $failedCount,
                'total_chunks' => count($chunks),
                'review_id' => $this->review->id,
                'current_status' => $this->review->status,
                'success_rate' => $processedCount / count($chunks)
            ]);

            if ($failedCount === 0) {
                // All chunks processed successfully
                Log::info('Marking review as completed', [
                    'action' => 'pr_review_marked_completed',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $prNumber,
                    'processed_chunks' => $processedCount,
                    'review_id' => $this->review->id,
                    'total_chunks' => count($chunks),
                    'success_rate' => '100%'
                ]);
                $this->review->markAsCompleted($processedCount);

                // Verify the status was updated
                $this->review->refresh();
                Log::info('Review status after completion', [
                    'action' => 'pr_review_status_verified',
                    'business_context' => 'ai_code_review',
                    'review_id' => $this->review->id,
                    'status' => $this->review->status,
                    'status_label' => $this->review->getStatusLabel(),
                    'processed_chunks' => $processedCount
                ]);
            } elseif ($processedCount === 0) {
                // All chunks failed
                $errorMessage = "All chunks failed to process. Errors: " . implode('; ', array_slice($chunkErrors, 0, 3));
                Log::warning('Marking review as failed', [
                    'action' => 'pr_review_marked_failed',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $prNumber,
                    'error' => $errorMessage,
                    'review_id' => $this->review->id,
                    'failed_chunks' => $failedCount,
                    'total_chunks' => count($chunks),
                    'success_rate' => '0%'
                ]);
                $this->review->markAsFailed($errorMessage);
            } else {
                // Partial success - mark as partial with error details
                $errorMessage = "Partial completion: {$processedCount}/" . count($chunks) . " chunks processed. Failed chunks: " . implode('; ', array_slice($chunkErrors, 0, 3));
                Log::warning('Marking review as partial', [
                    'action' => 'pr_review_marked_partial',
                    'business_context' => 'ai_code_review',
                    'repo' => $repo,
                    'pr_number' => $prNumber,
                    'processed_chunks' => $processedCount,
                    'total_chunks' => count($chunks),
                    'error' => $errorMessage,
                    'review_id' => $this->review->id,
                    'success_rate' => round(($processedCount / count($chunks)) * 100, 2) . '%'
                ]);
                $this->review->markAsPartial($processedCount, $errorMessage);
            }

            // Final status verification before job completion
            $this->review->refresh();
            Log::info('Job completion - final status verification', [
                'action' => 'pr_job_completion_verified',
                'business_context' => 'ai_code_review',
                'repo' => $repo,
                'pr_number' => $prNumber,
                'review_id' => $this->review->id,
                'final_status' => $this->review->status,
                'final_status_label' => $this->review->getStatusLabel(),
                'processed_chunks' => $this->review->processed_chunks,
                'total_chunks' => $this->review->chunks_count,
                'job_success' => $this->review->status === 'completed'
            ]);

        } catch (ConnectionException $e) {
            Log::error('Connection error during PR processing', [
                'action' => 'pr_connection_error',
                'business_context' => 'ai_code_review',
                'repo' => $this->payload['repository']['full_name'] ?? 'unknown',
                'pr_number' => $this->payload['number'] ?? 'unknown',
                'error' => $e->getMessage(),
                'review_id' => $this->review->id,
                'current_status' => $this->review->status,
                'error_type' => 'connection_error',
                'retry_delay' => 60
            ]);
            $this->review->markAsFailed('Connection error: ' . $e->getMessage());
            $this->release(60); // Retry after 60 seconds
            throw $e;

        } catch (Throwable $e) {
            Log::error('Failed to process pull request', [
                'action' => 'pr_processing_failed',
                'business_context' => 'ai_code_review',
                'repo' => $this->payload['repository']['full_name'] ?? 'unknown',
                'pr_number' => $this->payload['number'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'review_id' => $this->review->id,
                'current_status' => $this->review->status,
                'error_type' => 'processing_error'
            ]);
            $this->review->markAsFailed('Processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createReviewRecord(): PullRequestReview
    {
        return PullRequestReview::create([
            'repo_name' => $this->payload['repository']['full_name'],
            'pr_number' => $this->payload['number'],
            'commit_sha' => $this->payload['head']['sha'] ?? null,
            'action' => $this->payload['action'],
            'payload' => $this->payload,
            'status' => PullRequestReview::STATUS_PENDING,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Pull request job failed permanently', [
            'action' => 'pr_job_permanently_failed',
            'business_context' => 'ai_code_review',
            'repo' => $this->payload['repository']['full_name'] ?? 'unknown',
            'pr_number' => $this->payload['number'] ?? 'unknown',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_attempts' => $this->tries,
            'error_type' => 'job_failure'
        ]);
    }
}
