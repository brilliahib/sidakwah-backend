<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialContentRevision extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUPERSEDED = 'superseded';

    protected $fillable = [
        'material_content_id',
        'version',
        'sub_modul_id',
        'title',
        'youtube_link',
        'article_title',
        'article_content',
        'article_images',
        'status',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function materialContent()
    {
        return $this->belongsTo(MaterialContent::class);
    }

    public function subModul()
    {
        return $this->belongsTo(SubModul::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
