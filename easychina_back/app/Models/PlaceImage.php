<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceImage extends Model
{
    protected $fillable = [
        'place_id',
        'image_url',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
