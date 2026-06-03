@extends('admin.layout')

@section('title', 'Users with Role: ' . ucfirst($role->name))

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Users with Role: {{ ucfirst($role->name) }}</h1>
                <p class="mt-1 text-gray-500">{{ $role->description }}</p>
            </div>
            <a href="{{ route('admin.roles.show', $role) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Back to Role
            </a>
        </div>
    </div>

    <!-- Role Summary -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-blue-900">Role Information</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <div class="flex items-center space-x-4">
                        <span><strong>Status:</strong>
                            @if($role->is_active)
                                <span class="ml-1 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="ml-1 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                            @endif
                        </span>
                        <span><strong>Permissions:</strong> {{ count($role->permissions ?? []) }}</span>
                        <span><strong>Total Users:</strong> {{ $users->total() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            All Roles
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assigned to Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            @if($user->is_admin)
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Admin</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $userRole)
                                        <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">
                                            {{ ucfirst($userRole->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $user?->pivot?->created_at?->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $user?->pivot?->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                    View User
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    No users assigned to this role.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $users->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $users->firstItem() }}</span> to
                            <span class="font-medium">{{ $users->lastItem() }}</span> of
                            <span class="font-medium">{{ $users->total() }}</span> users
                        </p>
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
