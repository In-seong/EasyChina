<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function index(Request $request)
    {
        $query = Tip::with(['tipCategory', 'city'])
            ->when($request->tip_category_id, fn($q) => $q->where('tip_category_id', $request->tip_category_id))
            ->orderBy('tip_category_id')
            ->orderBy('sort_order');

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tip_category_id' => 'required|exists:tip_categories,id',
            'city_id' => 'nullable|exists:cities,id',
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'image_url' => 'nullable|string|max:500',
            'sort_order' => 'integer',
            'status' => 'in:PUBLIC,PRIVATE',
        ]);

        $tip = Tip::create($validated);
        return response()->json(['success' => true, 'data' => $tip->load(['tipCategory', 'city'])], 201);
    }

    public function show(Tip $tip)
    {
        return response()->json([
            'success' => true,
            'data' => $tip->load(['tipCategory', 'city']),
        ]);
    }

    public function update(Request $request, Tip $tip)
    {
        $validated = $request->validate([
            'tip_category_id' => 'exists:tip_categories,id',
            'city_id' => 'nullable|exists:cities,id',
            'title' => 'string|max:200',
            'content' => 'string',
            'image_url' => 'nullable|string|max:500',
            'sort_order' => 'integer',
            'status' => 'in:PUBLIC,PRIVATE',
        ]);

        $tip->update($validated);
        return response()->json(['success' => true, 'data' => $tip->load(['tipCategory', 'city'])]);
    }

    public function destroy(Tip $tip)
    {
        $tip->delete();
        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }
}
