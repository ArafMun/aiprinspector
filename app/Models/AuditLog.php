<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'data',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an audit action.
     */
    public static function log(string $action, array $data = []): self
    {
        $logData = array_merge([
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ], $data);

        if (auth()->check()) {
            $logData['user_id'] = auth()->id();
        }

        return static::create([
            'action' => $action,
            'data' => $logData,
        ]);
    }

    /**
     * Scope to get logs by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get logs within date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public static function getCountByDate(string $date): int
    {
        try {
            return self::whereDate('created_at', $date)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
