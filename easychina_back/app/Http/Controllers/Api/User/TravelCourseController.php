<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\TravelCourse;
use App\Models\TravelCourseItem;
use Illuminate\Http\Request;

class TravelCourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = $request->user()
            ->travelCourses()
            ->withCount('items')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'memo' => 'nullable|string',
        ]);

        $course = $request->user()->travelCourses()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $course,
            'message' => '여행 코스가 생성되었습니다.',
        ], 201);
    }

    public function show(TravelCourse $travelCourse)
    {
        $travelCourse->load(['items.place.city', 'items.place.category', 'items.place.primaryImage']);

        return response()->json([
            'success' => true,
            'data' => $travelCourse,
        ]);
    }

    public function update(Request $request, TravelCourse $travelCourse)
    {
        $validated = $request->validate([
            'title' => 'string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'memo' => 'nullable|string',
        ]);

        $travelCourse->update($validated);

        return response()->json([
            'success' => true,
            'data' => $travelCourse,
        ]);
    }

    public function destroy(TravelCourse $travelCourse)
    {
        $travelCourse->delete();
        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }

    public function addItem(Request $request, TravelCourse $travelCourse)
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'day_number' => 'nullable|integer|min:1',
            'memo' => 'nullable|string|max:300',
        ]);

        $validated['sort_order'] = $travelCourse->items()
            ->where('day_number', $validated['day_number'] ?? null)
            ->count();
        $validated['created_at'] = now();

        $item = $travelCourse->items()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $item->load('place'),
            'message' => '장소가 추가되었습니다.',
        ], 201);
    }

    public function removeItem(TravelCourse $travelCourse, TravelCourseItem $item)
    {
        $item->delete();
        return response()->json(['success' => true, 'message' => '장소가 제거되었습니다.']);
    }
}
