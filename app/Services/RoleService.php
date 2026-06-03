<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    public function getFilteredRoles(array $filters): LengthAwarePaginator
    {
        return Role::getFilteredWithUserCount($filters);
    }

    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => strtolower($data['name']),
            'description' => $data['description'],
            'permissions' => $data['permissions'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->logRoleAction('role_created', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update([
            'name' => strtolower($data['name']),
            'description' => $data['description'],
            'permissions' => $data['permissions'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->logRoleAction('role_updated', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->hasUsers()) {
            return false;
        }

        $roleName = $role->name;
        $role->delete();

        $this->logRoleAction('role_deleted', [
            'role_name' => $roleName,
        ]);

        return true;
    }

    public function toggleRoleStatus(Role $role): Role
    {
        $role->update([
            'is_active' => ! $role->is_active,
        ]);

        $this->logRoleAction('role_status_toggled', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'new_status' => $role->is_active,
        ]);

        return $role;
    }

    public function getRoleUsers(Role $role): LengthAwarePaginator
    {
        return $role->getUsersWithRoles(25);
    }

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
