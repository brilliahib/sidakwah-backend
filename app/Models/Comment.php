<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'material_content_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
        ];
    }

    public function materialContent(): BelongsTo
    {
        return $this->belongsTo(MaterialContent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->role === 'admin') {
            return $query;
        }

        return $query->where(function (Builder $visibleQuery) use ($viewer) {
            $visibleQuery
                ->where('status', self::STATUS_APPROVED)
                ->orWhere('user_id', $viewer->id);
        });
    }
}
