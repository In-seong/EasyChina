<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phrase extends Model
{
    protected $fillable = [
        'phrase_category_id',
        'text_ko',
        'text_cn',
        'pinyin',
        'sort_order',
        'status',
    ];

    public function phraseCategory()
    {
        return $this->belongsTo(PhraseCategory::class);
    }

    public function scopePublic($query)
    {
        return $query->where('status', 'PUBLIC');
    }
}
