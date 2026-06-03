@extends('admin.layout')

@section('title', 'My Profile')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <a href="{{ route('admin.profile.edit') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Edit Profile
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6">
            <!-- Profile Header -->
            <div class="flex items-center mb-6">
                <div class="w-20 h-20 bg-indigo-500 rounded-full flex items-center justify-center">
                    <span class="text-3xl font-medium text-white">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div class="ml-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    @if($user->is_admin)
                        <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                            Administrator
                        </span>
                    @endif
                </div>
            </div>

            <!-- Profile Details -->
            <div class="border-t border-gray-200 pt-6">
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
            </div>

            <!-- Roles -->
            @if($user->roles->count() > 0)
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Roles</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->roles as $role)
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Permissions -->
            @if($user->getPermissions())
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->getPermissions() as $permission)
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                            {{ $permission }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
