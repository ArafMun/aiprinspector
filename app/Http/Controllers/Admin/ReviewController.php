<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PullRequestReview;
use App\Jobs\ProcessPullRequestJob;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = PullRequestReview::with(['repo']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by repository
        if ($request->filled('repo')) {
            $query->where('repo_name', 'like', '%' . $request->repo . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by PR number
        if ($request->filled('pr_number')) {
            $query->where('pr_number', $request->pr_number);
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginate
        $reviews = $query->paginate(25)->withQueryString();

        // Get statistics for filters
        $statuses = PullRequestReview::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $repositories = PullRequestReview::select('repo_name')
            ->distinct()
            ->orderBy('repo_name')
            ->pluck('repo_name');

        return view('admin.reviews.index', compact('reviews', 'statuses', 'repositories'));
    }

    public function show(PullRequestReview $review)
    {
        // Load review with relationships
        $review->load(['repo']);

        // Get processing logs from the job
        $logs = $this->getReviewLogs($review);

        // Get AI provider information
        $aiProvider = $this->getAIProviderForReview($review);

        return view('admin.reviews.show', compact('review', 'logs', 'aiProvider'));
    }

    public function retry(PullRequestReview $review)
    {
        // Only allow retry for failed reviews
        if ($review->status !== PullRequestReview::STATUS_FAILED) {
            return redirect()->back()
                ->with('error', 'Only failed reviews can be retried.');
        }

        // Reset the review status
        $review->update([
            'status' => PullRequestReview::STATUS_PENDING,
            'error_message' => null,
            'processed_chunks' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);

        // Dispatch the job again
        ProcessPullRequestJob::dispatch($review->payload, $review);

        return redirect()->route('admin.reviews.show', $review)
            ->with('success', 'Review has been queued for retry.');
    }

    private function getReviewLogs(PullRequestReview $review): array
    {
        $logs = [];

        // Try to get logs from Laravel log file
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

        return array_reverse(array_slice($logs, -50)); // Last 50 matching lines
    }

    private function getAIProviderForReview(PullRequestReview $review): ?string
    {
        // Try to determine which AI provider was used
        // This could be enhanced by storing provider info in the review
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

    public function destroy(PullRequestReview $review)
    {
        // Only allow deletion of old failed reviews
        if ($review->status !== PullRequestReview::STATUS_FAILED ||
            $review->created_at->gt(now()->subDays(30))) {
            return redirect()->back()
                ->with('error', 'Only failed reviews older than 30 days can be deleted.');
        }

        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review has been deleted.');
    }
}
