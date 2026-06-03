<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $roles = $this->roleService->getFilteredRoles($filters);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $availablePermissions = Role::getAvailablePermissions();

        return view('admin.roles.create', compact('availablePermissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['required', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:'.implode(',', array_keys(Role::getAvailablePermissions()))],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->roleService->createRole([
            'name' => $request->name,
            'description' => $request->description,
            'permissions' => $request->permissions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('users');

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $availablePermissions = Role::getAvailablePermissions();

        return view('admin.roles.edit', compact('role', 'availablePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'description' => ['required', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:'.implode(',', array_keys(Role::getAvailablePermissions()))],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->roleService->updateRole($role, [
            'name' => $request->name,
            'description' => $request->description,
            'permissions' => $request->permissions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (! $this->roleService->deleteRole($role)) {
            return back()->with('error', 'Cannot delete role that has assigned users. Please reassign users first.');
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function users(Role $role)
    {
        $users = $this->roleService->getRoleUsers($role);

        return view('admin.roles.users', compact('role', 'users'));
    }

    public function toggleStatus(Role $role)
    {
        $role = $this->roleService->toggleRoleStatus($role);
        $status = $role->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Role {$role->name} {$status} successfully.");
    }
}
