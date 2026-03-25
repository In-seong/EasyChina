# EasyChina - Claude CLI Instructions

## Project Overview
중국 여행 올인원 앱. 한국어로 중국여행을 쉽게.
- 고덕지도(AMap) 네이티브 SDK + Vue 웹뷰 하이브리드 구조
- 사용자 앱(웹뷰+네이티브) + 관리자 웹(Vue SPA)

## Project Structure
```
EasyChina/
├── easychina_front/        # Vue 3 + TypeScript + Vite (Dual SPA: user/admin)
├── easychina_back/         # Laravel 12 (API Server)
├── EasyChina_App/          # iOS(SwiftUI) + Android(Java) 래퍼앱
│   ├── ios/
│   └── android/
├── CLAUDE.md               # 이 파일
├── DEV_REFERENCE.md        # 코딩 컨벤션
└── DB_SCHEMA.md            # DB 스키마 문서
```

## Architecture
- Frontend: Vue 3 + TypeScript + Vite + Tailwind CSS 4
- Backend: Laravel 12 + MySQL + Sanctum (Token Auth)
- Native: iOS(SwiftUI + AMap SDK), Android(Java + AMap SDK)
- Communication: JS Bridge (WebView <-> Native Map)

## Service Roles
| Role | Platform | Frontend Path | API Prefix |
|------|----------|--------------|------------|
| User (여행자) | App (WebView+Native) | `src/user/` | `/api/user/*` |
| Admin (운영자) | Web | `src/admin/` | `/api/admin/*` |

## Critical Rules
1. DB 변경 시 반드시 DB_SCHEMA.md 업데이트
2. DEV_REFERENCE.md의 코딩 컨벤션 준수
3. API 응답은 항상 `{ success, data?, message?, errors? }` 형식
4. 모든 좌표는 GCJ-02 기준 (중국 지도 표준)
5. 장소 데이터는 반드시 한국어 + 중국어 병기

## API Response Format
```json
{
  "success": true,
  "data": {},
  "message": "성공 메시지",
  "errors": null
}
```

## Naming Conventions
- Backend: snake_case (Laravel convention)
- Frontend: camelCase (TypeScript convention)
- DB columns: snake_case
- API endpoints: kebab-case (e.g., `/api/user/travel-courses`)
- Components: PascalCase (e.g., `PlaceCard.vue`)
