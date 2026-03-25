<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = $request->user()
            ->bookmarks()
            ->with(['city', 'category', 'primaryImage'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookmarks,
        ]);
    }

    public function toggle(Request $request, Place $place)
    {
        $user = $request->user();
        $exists = $user->bookmarks()->where('place_id', $place->id)->exists();

        if ($exists) {
            $user->bookmarks()->detach($place->id);
            $place->decrement('bookmark_count');
            $message = '북마크가 해제되었습니다.';
            $bookmarked = false;
        } else {
            $user->bookmarks()->attach($place->id, ['created_at' => now()]);
            $place->increment('bookmark_count');
            $message = '북마크에 추가되었습니다.';
            $bookmarked = true;
        }

        return response()->json([
            'success' => true,
            'data' => ['bookmarked' => $bookmarked],
            'message' => $message,
        ]);
    }
}
