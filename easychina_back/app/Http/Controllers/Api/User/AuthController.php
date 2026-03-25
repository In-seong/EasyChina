<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nickname' => 'required|string|max:50',
            'device_token' => 'nullable|string|max:255',
        ]);

        $user = User::create($validated);
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ], 201);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('defaultCity'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'nickname' => 'string|max:50',
            'default_city_id' => 'nullable|exists:cities,id',
            'device_token' => 'nullable|string|max:255',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'data' => $request->user()->fresh()->load('defaultCity'),
        ]);
    }
}
