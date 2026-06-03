<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\PasswordResetNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'profile_data'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->where('is_active', true)
            ->get()
            ->contains(function ($role) use ($permission) {
                return $role->hasPermission($permission);
            });
    }

    /**
     * Check if user is admin (backward compatibility).
     */
    public function isAdmin(): bool
    {
        return $this->is_admin || $this->hasRole('admin');
    }

    /**
     * Get all user permissions.
     */
    public function getPermissions(): array
    {
        return $this->roles()
            ->where('is_active', true)
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique()
            ->toArray();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'profile_data' => 'array',
        ];
    }

    public static function getFilteredWithRoles(array $filters): LengthAwarePaginator
    {
        $query = self::with('roles');

        if (! empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate(25)->withQueryString();
    }

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    public static function getActiveCount(int $minutes): int
    {
        try {
            return self::where('last_login_at', '>=', now()->subMinutes($minutes))->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getTotalCount(): int
    {
        try {
            return self::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getAdminCount(): int
    {
        try {
            return self::where('is_admin', true)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getRecent(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
