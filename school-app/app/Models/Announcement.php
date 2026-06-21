<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'type', 'target_audience', 'target_class_id',
        'published_by', 'published_at', 'expires_at', 'is_pinned', 'sms_sent',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_pinned' => 'boolean',
        'sms_sent' => 'boolean',
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function targetClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'target_class_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /**
     * Scope: only active (published and not expired) announcements.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /**
     * Scope: announcements visible to a given audience type.
     */
    public function scopeForAudience(Builder $query, string $audience): Builder
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $audience);
        });
    }

    /**
     * Scope: pinned announcements first.
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Check if a user has read this announcement.
     */
    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }
}
