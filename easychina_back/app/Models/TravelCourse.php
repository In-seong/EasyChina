<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelCourse extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'start_date',
        'end_date',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TravelCourseItem::class)->orderBy('day_number')->orderBy('sort_order');
    }
}
