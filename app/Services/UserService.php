<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getFilteredUsers(array $filters): LengthAwarePaginator
    {
        return User::getFilteredWithRoles($filters);
    }

    public function getActiveRoles(): Collection
    {
        return Role::active()->get();
    }

    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => $data['is_admin'] ?? false,
        ]);

        if (! empty($data['roles'])) {
            $user->roles()->attach($data['roles']);
        }

        $this->logUserAction('user_created', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $data['roles'] ?? [],
        ]);

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_admin' => $data['is_admin'] ?? false,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $roles = $data['roles'] ?? [];
        if (! empty($roles)) {
            $user->roles()->sync($roles);
        } else {
            $user->roles()->detach();
        }

        $this->logUserAction('user_updated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles' => $roles,
        ]);

        return $user;
    }

    public function deleteUser(User $user, int $currentUserId): bool
    {
        if ($user->id === $currentUserId) {
            return false;
        }

        $userEmail = $user->email;
        $user->delete();

        $this->logUserAction('user_deleted', [
            'user_email' => $userEmail,
        ]);

        return true;
    }

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
