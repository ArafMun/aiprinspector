@extends('admin.layout')

@section('title', 'Create User')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create User</h1>
        <p class="mt-1 text-gray-500">Add a new user to the system</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="px-6 py-4 space-y-6">
                <!-- User Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Roles & Permissions</h3>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                            <div class="flex items-center">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="role_{{ $role->id }}" class="ml-3">
                                    <span class="font-medium text-gray-900">{{ ucfirst($role->name) }}</span>
                                    <span class="text-gray-500">- {{ $role->description }}</span>
                                </label>
                            </div>
                            @if($role->permissions)
                                <div class="ml-7 text-sm text-gray-500">
                                    Permissions: {{ implode(', ', array_keys($role->permissions)) }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Admin Status -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Status</h3>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_admin" id="is_admin" value="1"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_admin" class="ml-3">
                            <span class="font-medium text-gray-900">Administrator</span>
                            <span class="text-gray-500">- Grant full administrative access</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
