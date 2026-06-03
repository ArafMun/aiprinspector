@extends('admin.layout')

@section('title', 'Edit Profile')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
            <a href="{{ route('admin.profile') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancel
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form action="{{ route('admin.profile.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $user->name) }}"
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
                               value="{{ old('email', $user->email) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.profile') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
