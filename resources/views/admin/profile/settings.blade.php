@extends('admin.layout')

@section('title', 'Settings')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
    </div>

    <div class="space-y-6">
        <!-- Change Password -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                <form action="{{ route('admin.settings.password') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="current_password" id="current_password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Joined</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F j, Y') }}</dd>
                    </div>
                    @if($user->last_login_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->last_login_at->format('F j, Y g:i A') }}</dd>
                    </div>
                    @endif
                </dl>
                <div class="mt-6">
                    <a href="{{ route('admin.profile.edit') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        Edit Profile &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
