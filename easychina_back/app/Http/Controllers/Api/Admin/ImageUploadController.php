<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaceImage;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function uploadPlaceImage(Request $request, Place $place)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_primary' => 'boolean',
        ]);

        $path = $request->file('image')->store("places/{$place->id}", 'public');

        if ($request->boolean('is_primary')) {
            $place->images()->update(['is_primary' => false]);
        }

        $image = PlaceImage::create([
            'place_id' => $place->id,
            'image_url' => '/storage/' . $path,
            'sort_order' => $place->images()->count(),
            'is_primary' => $request->boolean('is_primary', $place->images()->count() === 0),
        ]);

        return response()->json([
            'success' => true,
            'data' => $image,
            'message' => '이미지가 업로드되었습니다.',
        ], 201);
    }

    public function deletePlaceImage(PlaceImage $placeImage)
    {
        $path = str_replace('/storage/', '', $placeImage->image_url);
        Storage::disk('public')->delete($path);

        $placeImage->delete();

        return response()->json([
            'success' => true,
            'message' => '이미지가 삭제되었습니다.',
        ]);
    }

    public function uploadTipImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('image')->store('tips', 'public');

        return response()->json([
            'success' => true,
            'data' => ['image_url' => '/storage/' . $path],
        ]);
    }
}
