<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(25)->withQueryString();
        $roles = Role::active()->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::active()->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'is_admin' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin', false),
        ]);

        // Attach roles
        if ($request->filled('roles')) {
            $user->roles()->attach($request->roles);
        }

        // Log the action
        $this->logUserAction('user_created', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $request->roles ?? [],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::active()->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        \Log::info('UserController::update called', [
            'user_id' => $user->id,
            'request_data' => $request->all(),
            'request_method' => $request->method(),
        ]);

        // Filter out empty values from roles array before validation
        $filteredRequest = $request->all();
        if (isset($filteredRequest['roles']) && is_array($filteredRequest['roles'])) {
            $filteredRequest['roles'] = array_filter($filteredRequest['roles'], function($value) {
                return $value !== '' && $value !== null;
            });
            $filteredRequest['roles'] = array_values($filteredRequest['roles']); // Re-index array
        }

        $validator = Validator::make($filteredRequest, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'is_admin' => ['boolean'],
        ]);

        if ($validator->fails()) {
            \Log::info('UserController::update validation failed', [
                'errors' => $validator->errors()->all(),
            ]);
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('UserController::update before user update', [
            'current_name' => $user->name,
            'current_email' => $user->email,
            'new_name' => $request->name,
            'new_email' => $request->email,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->boolean('is_admin', false),
        ]);

        \Log::info('UserController::update after user update', [
            'updated_name' => $user->fresh()->name,
            'updated_email' => $user->fresh()->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }


        // Sync roles using the filtered roles array
        $roles = $filteredRequest['roles'] ?? [];

        if (!empty($roles)) {
            $user->roles()->sync($roles);
        } else {
            $user->roles()->detach();
        }

        // Log the action
        $this->logUserAction('user_updated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $roles,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the current user
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $userEmail = $user->email;
        $user->delete();

        // Log the action
        $this->logUserAction('user_deleted', [
            'user_email' => $userEmail,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Log user actions for audit trail.
     */
    private function logUserAction(string $action, array $data = []): void
    {
        $logData = array_merge([
            'action' => $action,
            'performed_by' => auth()->id(),
            'performed_by_email' => auth()->user()?->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data);

        \Log::info('User action performed', $logData);
    }
}
