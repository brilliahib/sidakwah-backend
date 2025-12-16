<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    protected $fillable = ['title', 'description'];

    public function subModuls()
    {
        return $this->hasMany(SubModul::class);
    }
}
