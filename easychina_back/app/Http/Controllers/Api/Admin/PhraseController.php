<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phrase;
use Illuminate\Http\Request;

class PhraseController extends Controller
{
    public function index(Request $request)
    {
        $query = Phrase::with('phraseCategory')
            ->when($request->phrase_category_id, fn($q) => $q->where('phrase_category_id', $request->phrase_category_id))
            ->orderBy('phrase_category_id')
            ->orderBy('sort_order');

        return response()->json([
            'success' => true,
            'data' => $query->paginate(50),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phrase_category_id' => 'required|exists:phrase_categories,id',
            'text_ko' => 'required|string|max:300',
            'text_cn' => 'required|string|max:300',
            'pinyin' => 'nullable|string|max:500',
            'sort_order' => 'integer',
            'status' => 'in:PUBLIC,PRIVATE',
        ]);

        $phrase = Phrase::create($validated);
        return response()->json(['success' => true, 'data' => $phrase], 201);
    }

    public function update(Request $request, Phrase $phrase)
    {
        $validated = $request->validate([
            'phrase_category_id' => 'exists:phrase_categories,id',
            'text_ko' => 'string|max:300',
            'text_cn' => 'string|max:300',
            'pinyin' => 'nullable|string|max:500',
            'sort_order' => 'integer',
            'status' => 'in:PUBLIC,PRIVATE',
        ]);

        $phrase->update($validated);
        return response()->json(['success' => true, 'data' => $phrase]);
    }

    public function destroy(Phrase $phrase)
    {
        $phrase->delete();
        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }
}
