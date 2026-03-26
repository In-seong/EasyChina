<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\User;

/*
|--------------------------------------------------------------------------
| User API (공개)
|--------------------------------------------------------------------------
*/
Route::prefix('user')->group(function () {
    Route::get('cities', [User\CityController::class, 'index']);
    Route::get('categories', [User\CategoryController::class, 'index']);
    Route::get('places', [User\PlaceController::class, 'index']);
    Route::get('places/{place}', [User\PlaceController::class, 'show']);
    Route::get('tip-categories', [User\TipController::class, 'categories']);
    Route::get('tip-categories/{tipCategory}', [User\TipController::class, 'show']);
    Route::get('phrase-categories', [User\PhraseController::class, 'categories']);
    Route::get('phrases', [User\PhraseController::class, 'index']);
    Route::get('banners', [User\BannerController::class, 'index']);

    // 사용자 인증
    Route::post('register', [User\AuthController::class, 'register']);

    // 로그인 필요
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [User\AuthController::class, 'me']);
        Route::put('me', [User\AuthController::class, 'updateProfile']);

        // 북마크
        Route::get('bookmarks', [User\BookmarkController::class, 'index']);
        Route::post('bookmarks/{place}', [User\BookmarkController::class, 'toggle']);

        // 여행 코스
        Route::apiResource('travel-courses', User\TravelCourseController::class);
        Route::post('travel-courses/{travelCourse}/items', [User\TravelCourseController::class, 'addItem']);
        Route::delete('travel-courses/{travelCourse}/items/{item}', [User\TravelCourseController::class, 'removeItem']);
    });
});

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::post('login', [Admin\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [Admin\AuthController::class, 'me']);
        Route::post('logout', [Admin\AuthController::class, 'logout']);
        Route::get('dashboard', [Admin\DashboardController::class, 'index']);

        // 기본 CRUD
        Route::apiResource('cities', Admin\CityController::class);
        Route::apiResource('categories', Admin\CategoryController::class);
        Route::apiResource('places', Admin\PlaceController::class);
        Route::apiResource('tip-categories', Admin\TipCategoryController::class);
        Route::apiResource('tips', Admin\TipController::class);
        Route::apiResource('phrase-categories', Admin\PhraseCategoryController::class);
        Route::apiResource('phrases', Admin\PhraseController::class);
        Route::apiResource('banners', Admin\BannerController::class);

        // 번역 도우미
        Route::post('translate/pinyin', [Admin\TranslateController::class, 'pinyin']);

        // 이미지 업로드
        Route::post('places/{place}/images', [Admin\ImageUploadController::class, 'uploadPlaceImage']);
        Route::delete('place-images/{placeImage}', [Admin\ImageUploadController::class, 'deletePlaceImage']);
        Route::post('upload/tip-image', [Admin\ImageUploadController::class, 'uploadTipImage']);
    });
});
