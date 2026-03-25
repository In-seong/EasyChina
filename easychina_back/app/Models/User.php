<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'nickname',
        'email',
        'provider',
        'provider_id',
        'device_token',
        'default_city_id',
    ];

    public function defaultCity()
    {
        return $this->belongsTo(City::class, 'default_city_id');
    }

    public function bookmarks()
    {
        return $this->belongsToMany(Place::class, 'bookmarks')->withPivot('created_at');
    }

    public function travelCourses()
    {
        return $this->hasMany(TravelCourse::class);
    }

    public function viewHistories()
    {
        return $this->hasMany(ViewHistory::class);
    }
}
