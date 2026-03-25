<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Phrase;
use App\Models\PhraseCategory;
use Illuminate\Http\Request;

class PhraseController extends Controller
{
    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => PhraseCategory::active()->ordered()->get(),
        ]);
    }

    public function index(Request $request)
    {
        $phrases = Phrase::public()
            ->when($request->phrase_category_id, fn($q) => $q->where('phrase_category_id', $request->phrase_category_id))
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $phrases,
        ]);
    }
}
