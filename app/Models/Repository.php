<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'url',
        'description',
        'default_branch',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get all pull request reviews for this repository.
     */
    public function pullRequestReviews(): HasMany
    {
        return $this->hasMany(PullRequestReview::class, 'repo_name', 'full_name');
    }

    /**
     * Get the count of reviews for this repository.
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->pullRequestReviews()->count();
    }

    /**
     * Get the count of completed reviews for this repository.
     */
    public function getCompletedReviewsCountAttribute(): int
    {
        return $this->pullRequestReviews()->where('status', PullRequestReview::STATUS_COMPLETED)->count();
    }

    /**
     * Get the count of failed reviews for this repository.
     */
    public function getFailedReviewsCountAttribute(): int
    {
        return $this->pullRequestReviews()->where('status', PullRequestReview::STATUS_FAILED)->count();
    }

    /**
     * Find or create a repository by full name.
     */
    public static function findOrCreateByName(string $fullName, array $data = []): self
    {
        return static::firstOrCreate(
            ['full_name' => $fullName],
            array_merge([
                'name' => explode('/', $fullName)[1] ?? $fullName,
                'is_active' => true,
            ], $data)
        );
    }

    /**
     * Scope to only include active repositories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
