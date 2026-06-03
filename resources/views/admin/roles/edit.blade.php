@extends('admin.layout')

@section('title', 'Edit Role')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Edit Role: {{ ucfirst($role->name) }}</h1>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.roles.show', $role) }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    View
                </a>
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Back to Roles
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $role->name) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="description" id="description" required
                               value="{{ old('description', $role->description) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Permissions Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="space-y-3">
                        @foreach($availablePermissions as $permission => $description)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="permission_{{ $permission }}" name="permissions[]" value="{{ $permission }}" type="checkbox"
                                           {{ in_array($permission, old('permissions', $role->permissions ?? [])) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="permission_{{ $permission }}" class="font-medium text-gray-900">
                                        {{ $permission }}
                                    </label>
                                    <p class="text-gray-500">{{ $description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('permissions')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="is_active" name="is_active" value="1" type="checkbox"
                               {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-900">
                            Active Role
                        </label>
                        <p class="text-gray-500">Role will be available for assignment to users</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between">
                <div>
                    @if($role->users->count() == 0)
                        <button type="button" onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete Role
                        </button>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Role
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
@if($role->users->count() == 0)
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
                            Delete Role
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete <strong>{{ ucfirst($role->name) }}</strong>?
                            </p>
                            <p class="text-sm text-red-600">This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeDeleteModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete Role
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
    form.action = '{{ route("admin.roles.destroy", $role) }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form);
    form.submit();
}
</script>
@endif

@endsection
