@extends('admin.layout')

@section('title', 'Statistics')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Statistics & Analytics</h1>
        <p class="mt-1 text-gray-500">Comprehensive analytics for AI code review system</p>
    </div>

    <!-- Time Range Selector -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-700">Time Range:</span>
            <div class="flex space-x-2">
                <a href="{{ route('admin.statistics', ['range' => 'today']) }}"
                   class="px-3 py-1 text-sm rounded-full {{ request('range') === 'today' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Today
                </a>
                <a href="{{ route('admin.statistics', ['range' => 'week']) }}"
                   class="px-3 py-1 text-sm rounded-full {{ !request('range') || request('range') === 'week' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    This Week
                </a>
                <a href="{{ route('admin.statistics', ['range' => 'month']) }}"
                   class="px-3 py-1 text-sm rounded-full {{ request('range') === 'month' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    This Month
                </a>
                <a href="{{ route('admin.statistics', ['range' => 'year']) }}"
                   class="px-3 py-1 text-sm rounded-full {{ request('range') === 'year' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    This Year
                </a>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach(['today', 'week', 'month', 'year'] as $range)
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 {{ $range === 'today' ? 'bg-blue-500' : ($range === 'week' ? 'bg-green-500' : ($range === 'month' ? 'bg-yellow-500' : 'bg-purple-500')) }} rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $statistics[$range]['total_reviews'] }}</div>
                        <div class="text-sm text-gray-500">{{ ucfirst($range) }} Reviews</div>
                        <div class="mt-1 flex items-center text-xs">
                            <span class="text-green-600">{{ $statistics[$range]['success_rate'] }}% success</span>
                            <span class="mx-1">•</span>
                            <span class="text-gray-500">{{ round($statistics[$range]['avg_processing_time']) }}s avg</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Hourly Activity Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Hourly Activity</h3>
            <div class="relative h-64">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Provider Usage Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">AI Provider Usage</h3>
            <div class="relative h-64">
                <canvas id="providerChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Repository Statistics -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Repository Statistics</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repository</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Reviews</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Processing Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($repoStats as $repo)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $repo->repo_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $repo->total_reviews }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $repo->completed_reviews }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($repo->total_reviews > 0)
                                    {{ round(($repo->completed_reviews / $repo->total_reviews) * 100, 1) }}%
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($repo->avg_processing_time)
                                    {{ round($repo->avg_processing_time, 1) }}s
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No repository data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Error Analysis -->
    @if($errorAnalysis->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Common Errors</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    @foreach($errorAnalysis as $error)
                        <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-red-800">
                                        {{ Str::limit($error->error_message, 100) }}
                                    </div>
                                    <div class="mt-1 text-xs text-red-600">
                                        Occurred {{ $error->count }} time{{ $error->count > 1 ? 's' : '' }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        {{ $error->count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load chart data
    loadChartData('{{ request('range', 'week') }}');

    // Auto-refresh charts every 30 seconds
    setInterval(() => {
        loadChartData('{{ request('range', 'week') }}');
    }, 30000);
});

function loadChartData(range) {
    fetch(`{{ route('admin.statistics.api') }}?range=${range}`)
        .then(response => response.json())
        .then(data => {
            renderHourlyChart(data.hourly_data);
            renderProviderChart(data.provider_usage);
        })
        .catch(error => console.error('Error loading chart data:', error));
}

function renderHourlyChart(hourlyData) {
    const ctx = document.getElementById('hourlyChart').getContext('2d');

    // Destroy existing chart if it exists
    if (window.hourlyChartInstance) {
        window.hourlyChartInstance.destroy();
    }

    window.hourlyChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourlyData.map(item => `${item.hour}:00`),
            datasets: [
                {
                    label: 'Total Reviews',
                    data: hourlyData.map(item => item.total),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: hourlyData.map(item => item.completed),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                },
                {
                    label: 'Failed',
                    data: hourlyData.map(item => item.failed),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function renderProviderChart(providerData) {
    const ctx = document.getElementById('providerChart').getContext('2d');

    // Destroy existing chart if it exists
    if (window.providerChartInstance) {
        window.providerChartInstance.destroy();
    }

    window.providerChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Claude', 'OpenAI'],
            datasets: [{
                data: [
                    providerData.claude.requests,
                    providerData.openai.requests
                ],
                backgroundColor: [
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(34, 197, 94, 0.8)'
                ],
                borderColor: [
                    'rgb(251, 146, 60)',
                    'rgb(34, 197, 94)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}
</script>
@endsection
