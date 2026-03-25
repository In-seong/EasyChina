<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipCategory;
use Illuminate\Http\Request;

class TipCategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => TipCategory::ordered()->withCount('tips')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer',
        ]);

        $cat = TipCategory::create($validated);
        return response()->json(['success' => true, 'data' => $cat], 201);
    }

    public function update(Request $request, TipCategory $tipCategory)
    {
        $validated = $request->validate([
            'name' => 'string|max:50',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $tipCategory->update($validated);
        return response()->json(['success' => true, 'data' => $tipCategory]);
    }

    public function destroy(TipCategory $tipCategory)
    {
        $tipCategory->delete();
        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }
}
