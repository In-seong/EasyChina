<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Place::with(['city', 'category'])
            ->when($request->city_id, fn($q) => $q->where('city_id', $request->city_id))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('updated_at');

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'category_id' => 'required|exists:categories,id',
            'name_ko' => 'required|string|max:100',
            'name_cn' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'pinyin' => 'nullable|string|max:200',
            'address_ko' => 'nullable|string|max:300',
            'address_cn' => 'required|string|max:300',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'phone' => 'nullable|string|max:30',
            'business_hours' => 'nullable|string|max:200',
            'closed_days' => 'nullable|string|max:100',
            'price_min' => 'nullable|integer',
            'price_max' => 'nullable|integer',
            'pay_alipay' => 'boolean',
            'pay_wechat' => 'boolean',
            'pay_cash' => 'boolean',
            'has_english_menu' => 'boolean',
            'restroom_rating' => 'nullable|integer|min:1|max:5',
            'description' => 'nullable|string',
            'tips' => 'nullable|string',
            'recommendation_score' => 'integer|min:1|max:100',
            'rating' => 'nullable|numeric|min:1|max:5',
            'status' => 'in:PUBLIC,PRIVATE,DRAFT',
        ]);

        $place = Place::create($validated);

        return response()->json([
            'success' => true,
            'data' => $place->load(['city', 'category']),
            'message' => '장소가 등록되었습니다.',
        ], 201);
    }

    public function show(Place $place)
    {
        return response()->json([
            'success' => true,
            'data' => $place->load(['city', 'category', 'images', 'tags']),
        ]);
    }

    public function update(Request $request, Place $place)
    {
        $validated = $request->validate([
            'city_id' => 'exists:cities,id',
            'category_id' => 'exists:categories,id',
            'name_ko' => 'string|max:100',
            'name_cn' => 'string|max:100',
            'name_en' => 'nullable|string|max:100',
            'pinyin' => 'nullable|string|max:200',
            'address_ko' => 'nullable|string|max:300',
            'address_cn' => 'string|max:300',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'phone' => 'nullable|string|max:30',
            'business_hours' => 'nullable|string|max:200',
            'closed_days' => 'nullable|string|max:100',
            'price_min' => 'nullable|integer',
            'price_max' => 'nullable|integer',
            'pay_alipay' => 'boolean',
            'pay_wechat' => 'boolean',
            'pay_cash' => 'boolean',
            'has_english_menu' => 'boolean',
            'restroom_rating' => 'nullable|integer|min:1|max:5',
            'description' => 'nullable|string',
            'tips' => 'nullable|string',
            'recommendation_score' => 'integer|min:1|max:100',
            'rating' => 'nullable|numeric|min:1|max:5',
            'status' => 'in:PUBLIC,PRIVATE,DRAFT',
        ]);

        $place->update($validated);

        return response()->json([
            'success' => true,
            'data' => $place->load(['city', 'category']),
            'message' => '장소가 수정되었습니다.',
        ]);
    }

    public function destroy(Place $place)
    {
        $place->delete();
        return response()->json(['success' => true, 'message' => '장소가 삭제되었습니다.']);
    }
}
