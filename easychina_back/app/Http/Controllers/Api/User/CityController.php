<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\City;

class CityController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => City::active()->ordered()->get(),
        ]);
    }
}
