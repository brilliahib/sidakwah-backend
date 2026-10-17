<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubModul extends Model
{
    protected $fillable = ['modul_id', 'title', 'description'];

    public function modul()
    {
        return $this->belongsTo(Modul::class);
    }

    public function materialContents()
    {
        return $this->hasMany(MaterialContent::class);
    }
}
