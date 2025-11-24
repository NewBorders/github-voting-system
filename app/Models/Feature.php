<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Feature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'title',
        'slug',
        'description',
        'status',
        'vote_count',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'vote_count' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Allowed status values.
     */
    public const STATUSES = [
        'submitted',
        'accepted',
        'planned',
        'in_progress',
        'done',
        'rejected',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Feature $feature) {
            if (empty($feature->slug)) {
                $feature->slug = static::generateUniqueSlug($feature->title, $feature->project_id);
            }
        });
    }

    /**
     * Generate a unique slug for this feature within its project.
     */
    protected static function generateUniqueSlug(string $title, int $projectId): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('project_id', $projectId)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the project that owns this feature.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all votes for this feature.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Check if a client has voted for this feature.
     */
    public function hasVoteFromClient(string $clientId): bool
    {
        return $this->votes()->where('client_id', $clientId)->exists();
    }

    /**
     * Add a vote from a client.
     */
    public function addVote(string $clientId): Vote
    {
        // Check if vote already exists
        $existingVote = $this->votes()->where('client_id', $clientId)->first();
        
        if ($existingVote) {
            return $existingVote;
        }

        $vote = $this->votes()->create([
            'client_id' => $clientId,
        ]);

        $this->increment('vote_count');

        return $vote;
    }

    /**
     * Remove a vote from a client.
     */
    public function removeVote(string $clientId): bool
    {
        $vote = $this->votes()->where('client_id', $clientId)->first();

        if (!$vote) {
            return false;
        }

        $vote->delete();
        $this->decrement('vote_count');

        // Ensure vote_count doesn't go negative
        if ($this->vote_count < 0) {
            $this->update(['vote_count' => 0]);
        }

        return true;
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    /**
     * Scope a query to sort features.
     */
    public function scopeSorted($query, string $sort = 'top')
    {
        return match ($sort) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'top' => $query->orderBy('vote_count', 'desc')->orderBy('created_at', 'desc'),
            'random' => $query->inRandomOrder(),
            default => $query->orderBy('vote_count', 'desc')->orderBy('created_at', 'desc'),
        };
    }
}
