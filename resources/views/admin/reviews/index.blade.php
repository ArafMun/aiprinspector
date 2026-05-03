@extends('admin.layout')

@section('title', 'Reviews')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Pull Request Reviews</h1>
            <div class="flex items-center space-x-2">
                <button onclick="window.location.reload()" class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Statuses</option>
                        @foreach([\App\Models\PullRequestReview::STATUS_PENDING => 'pending',
                                 \App\Models\PullRequestReview::STATUS_PROCESSING => 'processing',
                                 \App\Models\PullRequestReview::STATUS_COMPLETED => 'completed',
                                 \App\Models\PullRequestReview::STATUS_PARTIAL => 'partial',
                                 \App\Models\PullRequestReview::STATUS_FAILED => 'failed'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ ucfirst($label) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Repository</label>
                    <input type="text" name="repo" placeholder="Search repository..."
                           value="{{ request('repo') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PR Number</label>
                    <input type="number" name="pr_number" placeholder="PR number..."
                           value="{{ request('pr_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="flex-1 px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="flex-1 px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Clear
                    </a>
                </div>
                <div class="text-sm text-gray-500">
                    {{ $reviews->total() }} results found
                </div>
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.reviews.index', array_merge(request()->query(), ['order_by' => 'created_at', 'order_dir' => request('order_dir') == 'asc' ? 'desc' : 'asc'])) }}"
                               class="text-gray-500 hover:text-gray-700">
                                Created
                                @if(request('order_by') == 'created_at')
                                    <span>{{ request('order_dir') == 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repository</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $review->created_at->format('M j, Y H:i') }}</div>
                                <div class="text-gray-500">{{ $review->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $review->repo_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">#{{ $review->pr_number }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $review->status === \App\Models\PullRequestReview::STATUS_COMPLETED ? 'bg-green-100 text-green-800' :
                                       ($review->status === \App\Models\PullRequestReview::STATUS_FAILED ? 'bg-red-100 text-red-800' :
                                       ($review->status === \App\Models\PullRequestReview::STATUS_PROCESSING ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($review->getStatusLabel()) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($review->chunks_count > 0)
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($review->processed_chunks / $review->chunks_count) * 100 }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ $review->processed_chunks }}/{{ $review->chunks_count }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($review->started_at && $review->completed_at)
                                    {{ $review->started_at->diffInSeconds($review->completed_at) }}s
                                @elseif($review->started_at)
                                    {{ $review->started_at->diffInSeconds(now()) }}s (running)
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.reviews.show', $review) }}" class="text-indigo-600 hover:text-indigo-900">
                                        View
                                    </a>
                                    @if($review->status === \App\Models\PullRequestReview::STATUS_FAILED)
                                        <form action="{{ route('admin.reviews.retry', $review) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                                    onclick="return confirm('Retry this review?')">
                                                Retry
                                            </button>
                                        </form>
                                    @endif
                                    @if($review->status === \App\Models\PullRequestReview::STATUS_FAILED && $review->created_at->lt(now()->subDays(30)))
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Delete this failed review? This cannot be undone.')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div class="text-lg font-medium">No reviews found</div>
                                    <div class="text-sm">Try adjusting your filters or check back later.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $reviews->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $reviews->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $reviews->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $reviews->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $reviews->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
