<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'city_id',
        'category_id',
        'name_ko',
        'name_cn',
        'name_en',
        'pinyin',
        'address_ko',
        'address_cn',
        'latitude',
        'longitude',
        'phone',
        'business_hours',
        'closed_days',
        'price_min',
        'price_max',
        'pay_alipay',
        'pay_wechat',
        'pay_cash',
        'has_english_menu',
        'restroom_rating',
        'description',
        'tips',
        'recommendation_score',
        'rating',
        'status',
        'view_count',
        'bookmark_count',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'pay_alipay' => 'boolean',
            'pay_wechat' => 'boolean',
            'pay_cash' => 'boolean',
            'has_english_menu' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(PlaceImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PlaceImage::class)->where('is_primary', true);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'place_tags');
    }

    public function scopePublic($query)
    {
        return $query->where('status', 'PUBLIC');
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->when($cityId, fn($q) => $q->where('city_id', $cityId));
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->when($categoryId, fn($q) => $q->where('category_id', $categoryId));
    }
}
