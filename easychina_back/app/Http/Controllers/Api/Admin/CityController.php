<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => City::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ko' => 'required|string|max:50',
            'name_cn' => 'required|string|max:50',
            'name_en' => 'nullable|string|max:50',
            'pinyin' => 'nullable|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image_url' => 'nullable|string|max:500',
            'sort_order' => 'integer',
        ]);

        $city = City::create($validated);

        return response()->json([
            'success' => true,
            'data' => $city,
            'message' => '도시가 등록되었습니다.',
        ], 201);
    }

    public function show(City $city)
    {
        return response()->json([
            'success' => true,
            'data' => $city,
        ]);
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name_ko' => 'string|max:50',
            'name_cn' => 'string|max:50',
            'name_en' => 'nullable|string|max:50',
            'pinyin' => 'nullable|string|max:100',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'image_url' => 'nullable|string|max:500',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $city->update($validated);

        return response()->json([
            'success' => true,
            'data' => $city,
            'message' => '도시가 수정되었습니다.',
        ]);
    }

    public function destroy(City $city)
    {
        $city->delete();

        return response()->json([
            'success' => true,
            'message' => '도시가 삭제되었습니다.',
        ]);
    }
}
