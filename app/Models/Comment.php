<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'material_content_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function materialContent()
    {
        return $this->belongsTo(MaterialContent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->with(['user', 'replies']);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}
