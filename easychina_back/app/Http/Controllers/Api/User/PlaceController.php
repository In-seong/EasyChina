<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Place::public()
            ->with(['city', 'category', 'primaryImage'])
            ->byCity($request->city_id)
            ->byCategory($request->category_id)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('name_ko', 'like', "%{$request->search}%")
                       ->orWhere('name_cn', 'like', "%{$request->search}%")
                       ->orWhere('name_en', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('recommendation_score');

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->input('per_page', 6)),
        ]);
    }

    public function show(Place $place)
    {
        $place->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => $place->load(['city', 'category', 'images', 'tags']),
        ]);
    }
}
