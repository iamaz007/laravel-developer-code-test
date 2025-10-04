<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationValue extends Model
{
    protected $fillable = ['translation_key_id', 'locale_code', 'value', 'version'];

    public function key()
    {
        return $this->belongsTo(TranslationKey::class, 'translation_key_id');
    }

    public function scopeContentLike($q, string $term)
    {
        return $q->where('value', 'like', "%{$term}%");
    }

    public function scopeForLocale($q, string $code)
    {
        return $q->where('locale_code', $code);
    }
}
