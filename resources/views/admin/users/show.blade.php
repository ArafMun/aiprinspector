@extends('admin.layout')

@section('title', 'User Details')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">User Details: {{ $user->name }}</h1>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Edit
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Admin Status</dt>
                            <dd class="mt-1">
                                @if($user->is_admin)
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Admin User</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Regular User</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                            <dd class="mt-1">
                                @if($user->email_verified_at)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">{{ $user?->email_verified_at?->format('M d, Y') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Not Verified</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                            <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Roles & Permissions</h3>
                    @if($user->roles->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($user->roles as $role)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-md font-medium text-gray-900">{{ ucfirst($role->name) }}</h4>
                                        @if($role->is_active)
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3">{{ $role->description }}</p>
                                    @if($role->permissions)
                                        <div>
                                            <h5 class="text-xs font-medium text-gray-700 mb-2">Permissions:</h5>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($role->permissions as $permission)
                                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">{{ $permission }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No roles assigned</p>
                    @endif
                </div>

                @if($user->profile_data)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Data</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <pre class="text-sm text-gray-800 overflow-x-auto"><code>{{ json_encode($user->profile_data, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Edit User
                        </a>
                        @if(auth()->user()->id !== $user->id)
                            <button type="button" onclick="confirmDelete()" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Delete User
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Permissions Summary</h3>
                    @php
                        $permissions = $user->getPermissions();
                    @endphp
                    @if(count($permissions) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($permissions as $permission)
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ $permission }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No permissions assigned</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
@if(auth()->user()->id !== $user->id)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="deleteModal" style="display: none;">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Delete User
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete <strong>{{ $user->name }}</strong>?
                            </p>
                            <p class="text-sm text-red-600">This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeDeleteModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete User
                </button>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.users.destroy", $user) }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form);
    form.submit();
}
</script>
@endif
@endsection
