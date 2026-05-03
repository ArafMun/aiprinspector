@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Stats Overview -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_reviews'] }}</div>
                            <div class="text-sm text-gray-500">Total Reviews</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['completed_reviews'] }}</div>
                            <div class="text-sm text-gray-500">Completed</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['failed_reviews'] }}</div>
                            <div class="text-sm text-gray-500">Failed</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['processing_reviews'] }}</div>
                            <div class="text-sm text-gray-500">Processing</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['pending_reviews'] }}</div>
                            <div class="text-sm text-gray-500">Pending</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
                            <div class="text-sm text-gray-500">Total Users</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_roles'] }}</div>
                            <div class="text-sm text-gray-500">Total Roles</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Provider Status & Recent Reviews -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- AI Provider Status -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">AI Provider Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-900">Claude</h4>
                            <p class="text-sm text-gray-500">
                                Remaining: {{ $aiStatus['claude']['remaining_requests']['minute'] ?? 0 }}/min
                            </p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $aiStatus['claude']['configured'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <span class="ml-2 text-sm text-gray-600">
                                {{ $aiStatus['claude']['configured'] ? 'Configured' : 'Not Configured' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-900">OpenAI</h4>
                            <p class="text-sm text-gray-500">
                                Remaining: {{ $aiStatus['openai']['remaining_requests']['minute'] ?? 0 }}/min
                            </p>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $aiStatus['openai']['configured'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <span class="ml-2 text-sm text-gray-600">
                                {{ $aiStatus['openai']['configured'] ? 'Configured' : 'Not Configured' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Reviews</h3>
                    <a href="{{ route('admin.reviews.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentReviews as $review)
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-900">{{ $review->repo_name }}</span>
                                    <span class="ml-2 text-sm text-gray-500">#{{ $review->pr_number }}</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $review->status === \App\Models\PullRequestReview::STATUS_COMPLETED ? 'bg-green-100 text-green-800' :
                                           ($review->status === \App\Models\PullRequestReview::STATUS_FAILED ? 'bg-red-100 text-red-800' :
                                           ($review->status === \App\Models\PullRequestReview::STATUS_PROCESSING ? 'bg-yellow-100 text-yellow-800' :
                                           'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($review->getStatusLabel()) }}
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500">
                                        {{ $review->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('admin.reviews.show', $review) }}" class="text-indigo-600 hover:text-indigo-900">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No reviews yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Users & Role Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Users -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center mr-3">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles->take(2) as $role)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No users yet
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Role Distribution -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Role Distribution</h3>
                    <a href="{{ route('admin.roles.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Manage Roles</a>
                </div>
                <div class="space-y-3">
                    @forelse($roleDistribution as $role)
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">{{ ucfirst($role->name) }}</div>
                                <div class="text-sm text-gray-500">{{ $role->description }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-indigo-600">{{ $role->users_count }}</div>
                                <div class="text-xs text-gray-500">users</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            No roles found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Daily Trends Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Trends (Last 7 Days)</h3>
            <div class="relative h-64">
                <canvas id="dailyTrendsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Trends Chart
    const ctx = document.getElementById('dailyTrendsChart').getContext('2d');

    const dates = @json($dailyTrends->pluck('date'));
    const totals = @json($dailyTrends->pluck('total'));
    const completed = @json($dailyTrends->pluck('completed'));
    const failed = @json($dailyTrends->pluck('failed'));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString()),
            datasets: [
                {
                    label: 'Total Reviews',
                    data: totals,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Completed',
                    data: completed,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Failed',
                    data: failed,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
});
</script>
</div>
@endsection
