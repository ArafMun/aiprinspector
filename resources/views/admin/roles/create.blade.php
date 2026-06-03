@extends('admin.layout')

@section('title', 'Create Role')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Create New Role</h1>
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Back to Roles
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form action="{{ route('admin.roles.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name') }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Use lowercase, no spaces (e.g., 'admin', 'developer')</p>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="description" id="description" required
                               value="{{ old('description') }}"
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
                                           {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}
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
                               {{ old('is_active', true) ? 'checked' : '' }}
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
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format role name to lowercase
    const nameInput = document.getElementById('name');
    nameInput.addEventListener('input', function() {
        this.value = this.value.toLowerCase().replace(/\s+/g, '_');
    });
});
</script>
@endsection
