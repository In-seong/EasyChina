<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelCourseItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'travel_course_id',
        'place_id',
        'day_number',
        'sort_order',
        'memo',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function travelCourse()
    {
        return $this->belongsTo(TravelCourse::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
