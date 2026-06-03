@extends('admin.layout')

@section('title', 'Log Monitoring')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Log Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Log Monitoring</h1>
            <div class="flex items-center space-x-2">
                <button onclick="toggleAutoRefresh()" id="autoRefreshBtn"
                        class="px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                    Auto Refresh: OFF
                </button>
                <a href="{{ route('admin.logs.download') }}" class="px-3 py-2 text-sm bg-green-100 text-green-700 rounded-md hover:bg-green-200">
                    Download
                </a>
                <button onclick="if(confirm('Clear all logs? This cannot be undone.')) { window.location.href='{{ route('admin.logs.clear') }}' }"
                        class="px-3 py-2 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                    Clear Logs
                </button>
            </div>
        </div>
    </div>

    <!-- Log File Info -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">File Status</dt>
                <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $logInfo['exists'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $logInfo['exists'] ? 'Exists' : 'Not Found' }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">File Size</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $logInfo['size'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Last Modified</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $logInfo['modified'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total Lines</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ number_format($logInfo['lines']) }}</dd>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Log Level</label>
                    <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $filters['level'] === 'all' ? 'selected' : '' }}>All Levels</option>
                        <option value="error" {{ $filters['level'] === 'error' ? 'selected' : '' }}>Error & Critical</option>
                        <option value="warning" {{ $filters['level'] === 'warning' ? 'selected' : '' }}>Warning & Alert</option>
                        <option value="info" {{ $filters['level'] === 'info' ? 'selected' : '' }}>Info & Notice</option>
                        <option value="debug" {{ $filters['level'] === 'debug' ? 'selected' : '' }}>Debug</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" placeholder="Search logs..."
                           value="{{ $filters['search'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lines to Show</label>
                    <select name="lines" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="50" {{ $filters['lines'] === 50 ? 'selected' : '' }}>50 lines</option>
                        <option value="100" {{ $filters['lines'] === 100 ? 'selected' : '' }}>100 lines</option>
                        <option value="200" {{ $filters['lines'] === 200 ? 'selected' : '' }}>200 lines</option>
                        <option value="500" {{ $filters['lines'] === 500 ? 'selected' : '' }}>500 lines</option>
                        <option value="1000" {{ $filters['lines'] === 1000 ? 'selected' : '' }}>1000 lines</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Apply
                        </button>
                        <a href="{{ route('admin.logs.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Log Entries -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Log Entries</h3>
                <div class="text-sm text-gray-500">
                    Showing {{ count($logs) }} most recent entries
                </div>
            </div>
        </div>
        <div class="max-h-96 overflow-y-auto" id="logsContainer">
            @forelse($logs as $log)
                <div class="border-b border-gray-200 hover:bg-gray-50">
                    <div class="px-6 py-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $log['level'] === 'ERROR' || $log['level'] === 'CRITICAL' ? 'bg-red-100 text-red-800' :
                                       ($log['level'] === 'WARNING' || $log['level'] === 'ALERT' ? 'bg-yellow-100 text-yellow-800' :
                                       ($log['level'] === 'INFO' || $log['level'] === 'NOTICE' ? 'bg-blue-100 text-blue-800' :
                                       'bg-gray-100 text-gray-800')) }}">
                                    {{ $log['level'] }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    @if($log['timestamp'])
                                        <span>{{ $log['timestamp'] }}</span>
                                    @endif
                                    @if(!empty($log['context']))
                                        <button onclick="toggleContext({{ $loop->index }})" class="text-indigo-600 hover:text-indigo-900">
                                            Context
                                        </button>
                                    @endif
                                </div>
                                <div class="mt-1 text-sm text-gray-900">
                                    <pre class="whitespace-pre-wrap font-mono">{{ $log['message'] }}</pre>
                                </div>
                                @if(!empty($log['context']))
                                    <div id="context-{{ $loop->index }}" class="hidden mt-2">
                                        <details class="text-xs">
                                            <summary class="cursor-pointer text-gray-600 hover:text-gray-900">View Context</summary>
                                            <pre class="mt-2 p-2 bg-gray-50 rounded text-gray-700 overflow-x-auto">{{ json_encode($log['context'], JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div class="text-lg font-medium">No logs found</div>
                        <div class="text-sm">No log entries match your current filters.</div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Filters</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.logs.index', ['search' => 'pull request']) }}"
               class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                Pull Requests
            </a>
            <a href="{{ route('admin.logs.index', ['search' => 'claude']) }}"
               class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                Claude API
            </a>
            <a href="{{ route('admin.logs.index', ['search' => 'openai']) }}"
               class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                OpenAI API
            </a>
            <a href="{{ route('admin.logs.index', ['search' => 'rate limit']) }}"
               class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                Rate Limits
            </a>
            <a href="{{ route('admin.logs.index', ['search' => 'error']) }}"
               class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                Errors
            </a>
            <a href="{{ route('admin.logs.index', ['search' => 'queue']) }}"
               class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                Queue Jobs
            </a>
            <a href="{{ route('admin.logs.index', ['level' => 'error']) }}"
               class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-full hover:bg-red-200">
                Errors Only
            </a>
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

function toggleContext(index) {
    const element = document.getElementById(`context-${index}`);
    element.classList.toggle('hidden');
}

// Auto-refresh logs
document.addEventListener('DOMContentLoaded', function() {
    // Start with auto-refresh off for performance
    // Users can enable it manually
});
</script>
@endsection
