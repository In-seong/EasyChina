<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\TipCategory;

class TipController extends Controller
{
    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => TipCategory::active()->ordered()->get(),
        ]);
    }

    public function show(TipCategory $tipCategory)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $tipCategory,
                'tips' => $tipCategory->tips()
                    ->public()
                    ->with('city')
                    ->orderBy('sort_order')
                    ->get(),
            ],
        ]);
    }
}
