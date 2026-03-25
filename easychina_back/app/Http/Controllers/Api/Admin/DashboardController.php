<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Place;
use App\Models\Tip;
use App\Models\Phrase;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'cities' => City::count(),
                'places' => Place::count(),
                'tips' => Tip::count(),
                'phrases' => Phrase::count(),
            ],
        ]);
    }
}
