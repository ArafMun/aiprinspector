<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Scope to get only active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all available permissions.
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'admin.dashboard' => 'Access Admin Dashboard',
            'admin.reviews' => 'Manage Reviews',
            'admin.logs' => 'View Logs',
            'admin.users' => 'Manage Users',
            'admin.settings' => 'Manage Settings',
            'webhook.process' => 'Process Webhooks',
            'ai.configure' => 'Configure AI Services',
        ];
    }

    public static function getFilteredWithUserCount(array $filters): LengthAwarePaginator
    {
        $query = self::withCount('users');

        if (! empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate(25)->withQueryString();
    }

    public static function getTotalCount(): int
    {
        try {
            return self::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getActiveCount(): int
    {
        try {
            return self::where('is_active', true)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getRoleDistribution(): Collection
    {
        return self::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();
    }

    public function hasUsers(): bool
    {
        return $this->users()->count() > 0;
    }

    public function getUsersWithRoles(int $perPage = 25): LengthAwarePaginator
    {
        return $this->users()->with('roles')->paginate($perPage);
    }
}
