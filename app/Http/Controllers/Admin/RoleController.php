<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(25)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $availablePermissions = Role::getAvailablePermissions();
        return view('admin.roles.create', compact('availablePermissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['required', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(Role::getAvailablePermissions()))],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = Role::create([
            'name' => strtolower($request->name),
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Log the action
        $this->logRoleAction('role_created', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('users');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $availablePermissions = Role::getAvailablePermissions();
        return view('admin.roles.edit', compact('role', 'availablePermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'description' => ['required', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(Role::getAvailablePermissions()))],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $role->update([
            'name' => strtolower($request->name),
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Log the action
        $this->logRoleAction('role_updated', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of roles with users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role that has assigned users. Please reassign users first.');
        }

        $roleName = $role->name;
        $role->delete();

        // Log the action
        $this->logRoleAction('role_deleted', [
            'role_name' => $roleName,
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Show users with specific role.
     */
    public function users(Role $role)
    {
        $users = $role->users()->with('roles')->paginate(25);
        return view('admin.roles.users', compact('role', 'users'));
    }

    /**
     * Toggle role status.
     */
    public function toggleStatus(Role $role)
    {
        $role->update([
            'is_active' => !$role->is_active
        ]);

        $status = $role->is_active ? 'activated' : 'deactivated';

        // Log the action
        $this->logRoleAction('role_status_toggled', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'new_status' => $role->is_active,
        ]);

        return back()->with('success', "Role {$role->name} {$status} successfully.");
    }

    /**
     * Log role actions for audit trail.
     */
    private function logRoleAction(string $action, array $data = []): void
    {
        $logData = array_merge([
            'action' => $action,
            'performed_by' => auth()->id(),
            'performed_by_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data);

        \Log::channel('audit')->info('Role action performed', $logData);
    }
}
