<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Banner::active()
                ->with('city')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
