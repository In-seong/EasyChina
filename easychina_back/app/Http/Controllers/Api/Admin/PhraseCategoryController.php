<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhraseCategory;
use Illuminate\Http\Request;

class PhraseCategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => PhraseCategory::ordered()->withCount('phrases')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer',
        ]);

        $cat = PhraseCategory::create($validated);
        return response()->json(['success' => true, 'data' => $cat], 201);
    }

    public function update(Request $request, PhraseCategory $phraseCategory)
    {
        $validated = $request->validate([
            'name' => 'string|max:50',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $phraseCategory->update($validated);
        return response()->json(['success' => true, 'data' => $phraseCategory]);
    }

    public function destroy(PhraseCategory $phraseCategory)
    {
        $phraseCategory->delete();
        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }
}
