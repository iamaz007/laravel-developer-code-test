<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationKey extends Model
{
    protected $fillable = ['key', 'description'];

    public function values()
    {
        return $this->hasMany(TranslationValue::class, 'translation_key_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'key_tag');
    }

    public function scopeWithTag($q, string $tag)
    {
        return $q->whereHas('tags', fn ($t) => $t->where('name', $tag));
    }

    public function scopeKeyLike($q, string $term)
    {
        return $q->where('key', 'like', "%{$term}%");
    }

    public function scopeContentLike($q, string $term)
    {
        return $q->whereHas('values', fn ($v) => $v->where('value', 'like', "%{$term}%"));
    }
}
