<?php

namespace App\Models;

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
    ];

    public function subModul()
    {
        return $this->belongsTo(SubModul::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
