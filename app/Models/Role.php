<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
