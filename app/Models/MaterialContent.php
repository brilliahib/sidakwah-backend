<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MaterialContent extends Model
{
    protected $fillable = [
        'sub_modul_id',
        'title',
        'youtube_link',
        'article_title',
        'article_content',
        'article_images',
        'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function subModul()
    {
        return $this->belongsTo(SubModul::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function revisions()
    {
        return $this->hasMany(MaterialContentRevision::class)->orderByDesc('version');
    }

    public function latestRevision()
    {
        return $this->hasOne(MaterialContentRevision::class)->latestOfMany('version');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }
}
