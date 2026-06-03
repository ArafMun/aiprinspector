<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PullRequestReview;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'repo' => $request->get('repo'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'pr_number' => $request->get('pr_number'),
            'order_by' => $request->get('order_by'),
            'order_dir' => $request->get('order_dir'),
        ];

        $reviews = $this->reviewService->getFilteredReviews($filters);
        $statistics = $this->reviewService->getReviewStatistics();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statuses' => $statistics['statuses'],
            'repositories' => $statistics['repositories'],
        ]);
    }

    public function show(PullRequestReview $review)
    {
        $data = $this->reviewService->getReviewWithDetails($review);

        return view('admin.reviews.show', [
            'review' => $data['review'],
            'logs' => $data['logs'],
            'aiProvider' => $data['aiProvider'],
        ]);
    }

    public function retry(PullRequestReview $review)
    {
        if (! $this->reviewService->retryReview($review)) {
            return redirect()->back()
                ->with('error', 'Only failed reviews can be retried.');
        }

        return redirect()->route('admin.reviews.show', $review)
            ->with('success', 'Review has been queued for retry.');
    }

    public function destroy(PullRequestReview $review)
    {
        if (! $this->reviewService->deleteReview($review)) {
            return redirect()->back()
                ->with('error', 'Only failed reviews older than 30 days can be deleted.');
        }

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review has been deleted.');
    }
}
