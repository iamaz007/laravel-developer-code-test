<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];

    public function keys()
    {
        return $this->belongsToMany(TranslationKey::class, 'key_tag');
    }
}
