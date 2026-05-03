@extends('admin.layout')

@section('title', 'Review Details - ' . $review->repo_name . '#' . $review->pr_number)

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Review Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $review->repo_name }} #{{ $review->pr_number }}</h1>
                    <div class="mt-1 flex items-center space-x-4">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $review->status === \App\Models\PullRequestReview::STATUS_COMPLETED ? 'bg-green-100 text-green-800' :
                               ($review->status === \App\Models\PullRequestReview::STATUS_FAILED ? 'bg-red-100 text-red-800' :
                               ($review->status === \App\Models\PullRequestReview::STATUS_PROCESSING ? 'bg-yellow-100 text-yellow-800' :
                               'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($review->getStatusLabel()) }}
                        </span>
                        <span class="text-sm text-gray-500">
                            Created {{ $review->created_at->diffForHumans() }}
                        </span>
                        @if($aiProvider)
                            <span class="text-sm text-gray-500">
                                AI Provider: {{ ucfirst($aiProvider) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($review->status === \App\Models\PullRequestReview::STATUS_FAILED)
                        <form action="{{ route('admin.reviews.retry', $review) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                                    onclick="return confirm('Retry this review?')">
                                Retry Review
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Back to Reviews
                    </a>
                </div>
            </div>
        </div>

        <!-- Review Details -->
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Repository</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->repo_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Pull Request</dt>
                    <dd class="mt-1 text-sm text-gray-900">#{{ $review->pr_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Action</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->action }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Commit SHA</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $review->commit_sha ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Files Count</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->files_count ?? 0 }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Chunks Count</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->chunks_count ?? 0 }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Processed Chunks</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $review->processed_chunks ?? 0 }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Success Rate</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($review->chunks_count > 0)
                            {{ round(($review->processed_chunks / $review->chunks_count) * 100, 1) }}%
                        @else
                            N/A
                        @endif
                    </dd>
                </div>
            </div>

            @if($review->started_at)
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Started At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $review->started_at->format('M j, Y H:i:s') }}</dd>
                    </div>
                    @if($review->completed_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $review->completed_at->format('M j, Y H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Processing Duration</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $review->started_at->diffInSeconds($review->completed_at) }} seconds</dd>
                        </div>
                    @endif
                </div>
            @endif

            @if($review->error_message)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Error Message</dt>
                    <dd class="mt-1 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-800">
                        {{ $review->error_message }}
                    </dd>
                </div>
            @endif
        </div>
    </div>

    <!-- Progress Bar -->
    @if($review->chunks_count > 0)
        <div class="bg-white shadow rounded-lg mb-6 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Processing Progress</h3>
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-indigo-600 h-4 rounded-full flex items-center justify-center text-white text-xs font-medium"
                             style="width: {{ ($review->processed_chunks / $review->chunks_count) * 100 }}%">
                            {{ round(($review->processed_chunks / $review->chunks_count) * 100, 1) }}%
                        </div>
                    </div>
                </div>
                <div class="ml-4 text-sm text-gray-900">
                    {{ $review->processed_chunks }} / {{ $review->chunks_count }} chunks
                </div>
            </div>
        </div>
    @endif

    <!-- Processing Logs -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Processing Logs</h3>
                <button onclick="toggleAutoRefresh()" id="autoRefreshBtn"
                        class="px-3 py-1 text-sm bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                    Auto Refresh: OFF
                </button>
            </div>
        </div>
        <div class="px-6 py-4 max-h-96 overflow-y-auto" id="logsContainer">
            @forelse($logs as $log)
                <div class="mb-3 p-3 border-l-4
                    {{ str_contains($log, 'ERROR') ? 'border-red-500 bg-red-50' :
                       (str_contains($log, 'WARNING') ? 'border-yellow-500 bg-yellow-50' :
                       (str_contains($log, 'INFO') ? 'border-blue-500 bg-blue-50' :
                       'border-gray-500 bg-gray-50')) }}">
                    <pre class="text-sm whitespace-pre-wrap font-mono">{{ $log }}</pre>
                </div>
            @empty
                <div class="text-center text-gray-500 py-8">
                    No logs available for this review
                </div>
            @endforelse
        </div>
    </div>

    <!-- Webhook Payload (Collapsible) -->
    <div class="bg-white shadow rounded-lg mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <button onclick="togglePayload()" class="flex items-center justify-between w-full text-left">
                <h3 class="text-lg font-medium text-gray-900">Webhook Payload</h3>
                <svg id="payloadToggle" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        <div id="payloadContent" class="hidden px-6 py-4">
            <pre class="text-sm bg-gray-50 p-4 rounded-md overflow-x-auto">{{ json_encode($review->payload, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>

<script>
function toggleAutoRefresh() {
    const btn = document.getElementById('autoRefreshBtn');
    const isRefreshing = btn.textContent.includes('ON');

    if (isRefreshing) {
        stopAutoRefresh();
        btn.textContent = 'Auto Refresh: OFF';
        btn.classList.remove('bg-red-100', 'text-red-700');
        btn.classList.add('bg-indigo-100', 'text-indigo-700');
    } else {
        startAutoRefresh('{{ route('admin.logs.tail', ['lines' => 20]) }}', 'logsContainer', 3000);
        btn.textContent = 'Auto Refresh: ON';
        btn.classList.remove('bg-indigo-100', 'text-indigo-700');
        btn.classList.add('bg-red-100', 'text-red-700');
    }
}

function togglePayload() {
    const content = document.getElementById('payloadContent');
    const toggle = document.getElementById('payloadToggle');

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        toggle.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        toggle.classList.remove('rotate-180');
    }
}

// Auto-refresh if review is still processing
@if($review->status === \App\Models\PullRequestReview::STATUS_PROCESSING)
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            toggleAutoRefresh();
        }, 1000);
    });
@endif
</script>
@endsection
