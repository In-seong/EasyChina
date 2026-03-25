<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    protected $fillable = [
        'tip_category_id',
        'city_id',
        'title',
        'content',
        'image_url',
        'sort_order',
        'status',
    ];

    public function tipCategory()
    {
        return $this->belongsTo(TipCategory::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopePublic($query)
    {
        return $query->where('status', 'PUBLIC');
    }
}
