<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Banner::with('city')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'nullable|exists:cities,id',
            'content' => 'required|string|max:300',
            'type' => 'in:INFO,WARNING,URGENT',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $banner = Banner::create($validated);
        return response()->json(['success' => true, 'data' => $banner->load('city')], 201);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'city_id' => 'nullable|exists:cities,id',
            'content' => 'string|max:300',
            'type' => 'in:INFO,WARNING,URGENT',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $banner->update($validated);
        return response()->json(['success' => true, 'data' => $banner->load('city')]);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }
}
