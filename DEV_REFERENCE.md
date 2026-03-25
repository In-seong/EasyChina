# EasyChina 개발자 참고 문서

> 최종 업데이트: 2026-03-25

---

## 1. 프로젝트 구조

```
EasyChina/
├── easychina_front/        # Vue 3 + TypeScript (Dual SPA)
├── easychina_back/         # Laravel 12 (API Server)
├── EasyChina_App/          # iOS(SwiftUI) + Android(Java) 래퍼앱
└── docs/                   # 기획 문서
```

### 1.1 역할별 서비스 구분

| 역할 | 플랫폼 | 프론트엔드 경로 | API Prefix | 인증 방식 |
|------|--------|---------------|-----------|---------|
| User (여행자) | 앱 (WebView+Native) | `src/user/` | `/api/user/*` | Device UUID + Sanctum Token |
| Admin (운영자) | 웹 | `src/admin/` | `/api/admin/*` | Email/Password + Sanctum Token |

---

## 2. 백엔드 컨벤션 (Laravel 12)

### 2.1 디렉토리 구조

```
easychina_back/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── User/           # 사용자 API
│   │   │   │   ├── PlaceController.php
│   │   │   │   ├── TipController.php
│   │   │   │   ├── PhraseController.php
│   │   │   │   ├── BookmarkController.php
│   │   │   │   ├── TravelCourseController.php
│   │   │   │   └── BannerController.php
│   │   │   └── Admin/          # 관리자 API
│   │   │       ├── CityController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── PlaceController.php
│   │   │       ├── TipController.php
│   │   │       ├── PhraseController.php
│   │   │       ├── BannerController.php
│   │   │       └── UserController.php
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Services/
├── routes/
│   └── api.php
├── database/
│   ├── migrations/
│   └── seeders/
└── storage/
    └── app/public/         # 이미지 업로드
```

### 2.2 API 응답 형식

```php
// 성공
return response()->json([
    'success' => true,
    'data'    => $data,
    'message' => '조회 성공',
], 200);

// 실패
return response()->json([
    'success' => false,
    'message' => '에러 메시지',
    'errors'  => $validator->errors(),
], 422);
```

### 2.3 컨트롤러 패턴

```php
class PlaceController extends Controller
{
    public function index(Request $request)    // GET    /api/user/places
    public function show(Place $place)         // GET    /api/user/places/{id}
}

// Admin
class PlaceController extends Controller
{
    public function index(Request $request)    // GET    /api/admin/places
    public function store(Request $request)    // POST   /api/admin/places
    public function show(Place $place)         // GET    /api/admin/places/{id}
    public function update(Request $request, Place $place)  // PUT
    public function destroy(Place $place)      // DELETE /api/admin/places/{id}
}
```

### 2.4 이미지 업로드

```php
// 저장 경로: storage/app/public/places/{place_id}/
// URL 접근: /storage/places/{place_id}/filename.jpg
$path = $file->store("places/{$place->id}", 'public');
```

---

## 3. 프론트엔드 컨벤션 (Vue 3 + TypeScript)

### 3.1 디렉토리 구조

```
easychina_front/
├── src/
│   ├── user/                   # 사용자 앱
│   │   ├── views/
│   │   │   ├── MapView.vue         # 지도 (네이티브 브릿지)
│   │   │   ├── PlacesView.vue      # 여행지 목록
│   │   │   ├── PlaceDetailView.vue # 장소 상세
│   │   │   ├── TipsView.vue        # 여행수첩
│   │   │   ├── TipDetailView.vue   # 팁 상세
│   │   │   ├── PhrasesView.vue     # 번역 도우미
│   │   │   ├── MyPageView.vue      # 마이페이지
│   │   │   ├── BookmarksView.vue   # 북마크 목록
│   │   │   └── CourseView.vue      # 여행 코스
│   │   ├── components/
│   │   ├── router/
│   │   └── App.vue
│   ├── admin/                  # 관리자 패널
│   │   ├── views/
│   │   │   ├── DashboardView.vue
│   │   │   ├── CityListView.vue
│   │   │   ├── CategoryListView.vue
│   │   │   ├── PlaceListView.vue
│   │   │   ├── PlaceFormView.vue
│   │   │   ├── TipListView.vue
│   │   │   ├── TipFormView.vue
│   │   │   ├── PhraseListView.vue
│   │   │   ├── PhraseFormView.vue
│   │   │   ├── BannerListView.vue
│   │   │   └── UserListView.vue
│   │   ├── components/
│   │   ├── router/
│   │   └── App.vue
│   └── shared/                 # 공통
│       ├── api/                # API 호출 모듈
│       │   └── index.ts        # axios instance
│       ├── components/         # 공용 컴포넌트
│       ├── composables/        # 공용 composable
│       ├── types/              # TypeScript 타입
│       └── utils/              # 유틸리티
├── vite.user.config.ts
└── vite.admin.config.ts
```

### 3.2 컴포넌트 네이밍

```
Views:     XxxView.vue      (e.g., PlaceDetailView.vue)
Components: XxxYyy.vue      (e.g., PlaceCard.vue, TabBar.vue)
Composables: useXxx.ts      (e.g., useBookmark.ts)
Types:     xxx.ts           (e.g., place.ts)
```

### 3.3 API 호출 패턴

```typescript
// shared/api/index.ts
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { 'Content-Type': 'application/json' },
})

export default api

// 사용
const { data } = await api.get('/api/user/places', { params })
```

### 3.4 JS Bridge 패턴 (네이티브 통신)

```typescript
// shared/utils/bridge.ts
interface NativeBridge {
  openMap(): void
  navigateTo(lat: number, lng: number, name: string): void
  showPlaceOnMap(placeId: number, lat: number, lng: number): void
  getCurrentLocation(): Promise<{ lat: number; lng: number }>
  speakChinese(text: string): void
}

// iOS: window.webkit.messageHandlers
// Android: window.AndroidBridge
```

### 3.5 스타일

- Tailwind CSS 4 사용
- 모바일 퍼스트 (사용자 앱은 웹뷰이므로 모바일 화면 기준)
- 관리자 앱은 데스크톱 기준

---

## 4. 좌표 체계

중국은 GCJ-02 좌표계 사용 (의도적 오프셋).
- DB에 저장하는 모든 좌표: GCJ-02 기준
- AMap SDK: GCJ-02 네이티브 지원 (변환 불필요)
- GPS 원본(WGS-84)을 받으면 네이티브에서 GCJ-02로 변환 후 사용
