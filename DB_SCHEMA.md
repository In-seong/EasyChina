# EasyChina DB Schema

> 최종 업데이트: 2026-03-25
> DB: MySQL 8.0 | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci

---

## ERD 요약

```
cities ──< places ──< place_images
              │──< place_tags >── tags
              │──< bookmarks >── users
              │──< travel_course_items >── travel_courses >── users
              └──< view_histories >── users

categories ──< places

tip_categories ──< tips

phrase_categories ──< phrases

banners (독립)

admins (독립)
```

---

## 1. admins (관리자)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| name | varchar(50) | NO | | 이름 |
| email | varchar(100) | NO | | 이메일 (unique) |
| password | varchar(255) | NO | | 비밀번호 (hash) |
| role | enum('SUPER','ADMIN') | NO | 'ADMIN' | 권한 |
| is_active | tinyint(1) | NO | 1 | 활성 상태 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 2. users (사용자)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| nickname | varchar(50) | NO | | 닉네임 |
| email | varchar(100) | YES | | 이메일 (unique, nullable) |
| provider | varchar(20) | YES | | OAuth provider (kakao, apple 등) |
| provider_id | varchar(100) | YES | | OAuth ID |
| device_token | varchar(255) | YES | | FCM/APNs 토큰 |
| default_city_id | bigint FK | YES | | 기본 도시 설정 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 3. cities (도시)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| name_ko | varchar(50) | NO | | 한국어 이름 (베이징) |
| name_cn | varchar(50) | NO | | 중국어 이름 (北京) |
| name_en | varchar(50) | YES | | 영어 이름 (Beijing) |
| pinyin | varchar(100) | YES | | 병음 (Běijīng) |
| latitude | decimal(10,7) | NO | | 도시 중심 위도 (GCJ-02) |
| longitude | decimal(10,7) | NO | | 도시 중심 경도 (GCJ-02) |
| image_url | varchar(500) | YES | | 도시 대표 이미지 |
| sort_order | int | NO | 0 | 정렬 순서 |
| is_active | tinyint(1) | NO | 1 | 노출 여부 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 4. categories (장소 카테고리)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| name_ko | varchar(50) | NO | | 한국어 (관광지, 맛집, 카페...) |
| name_cn | varchar(50) | YES | | 중국어 |
| icon | varchar(50) | YES | | 아이콘 코드 |
| color | varchar(7) | YES | | 마커 색상 (#FF5733) |
| sort_order | int | NO | 0 | 정렬 순서 |
| is_active | tinyint(1) | NO | 1 | |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 5. places (장소)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| city_id | bigint FK | NO | | 도시 |
| category_id | bigint FK | NO | | 카테고리 |
| name_ko | varchar(100) | NO | | 한국어 이름 |
| name_cn | varchar(100) | NO | | 중국어 이름 |
| name_en | varchar(100) | YES | | 영어 이름 |
| pinyin | varchar(200) | YES | | 병음 |
| address_ko | varchar(300) | YES | | 한국어 주소 |
| address_cn | varchar(300) | NO | | 중국어 주소 |
| latitude | decimal(10,7) | NO | | 위도 (GCJ-02) |
| longitude | decimal(10,7) | NO | | 경도 (GCJ-02) |
| phone | varchar(30) | YES | | 전화번호 |
| business_hours | varchar(200) | YES | | 영업시간 텍스트 |
| closed_days | varchar(100) | YES | | 휴무일 |
| price_min | int | YES | | 최소 가격 (원) |
| price_max | int | YES | | 최대 가격 (원) |
| pay_alipay | tinyint(1) | NO | 1 | 알리페이 가능 |
| pay_wechat | tinyint(1) | NO | 1 | 위챗페이 가능 |
| pay_cash | tinyint(1) | NO | 0 | 현금 가능 |
| has_english_menu | tinyint(1) | NO | 0 | 영어메뉴 여부 |
| restroom_rating | tinyint | YES | | 화장실 청결도 (1-5) |
| description | text | YES | | 설명 (한국어) |
| tips | text | YES | | 여행 팁 (한국어) |
| recommendation_score | int | NO | 50 | 추천 점수 (1-100) |
| rating | decimal(2,1) | YES | | 별점 (1.0-5.0) |
| status | enum('PUBLIC','PRIVATE','DRAFT') | NO | 'DRAFT' | 상태 |
| view_count | int | NO | 0 | 조회수 |
| bookmark_count | int | NO | 0 | 북마크 수 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

### Index
- `idx_places_city_category` (city_id, category_id, status)
- `idx_places_status_recommendation` (status, recommendation_score DESC)
- `idx_places_coordinates` (latitude, longitude)

---

## 6. place_images (장소 이미지)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| place_id | bigint FK | NO | | 장소 |
| image_url | varchar(500) | NO | | 이미지 경로 |
| sort_order | int | NO | 0 | 정렬 순서 |
| is_primary | tinyint(1) | NO | 0 | 대표 이미지 여부 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 7. tags (태그)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| name | varchar(30) | NO | | 태그명 (unique) |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 8. place_tags (장소-태그 매핑)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| place_id | bigint FK | NO | | 장소 |
| tag_id | bigint FK | NO | | 태그 |

### Index
- `unique_place_tag` (place_id, tag_id) UNIQUE

---

## 9. tip_categories (팁 카테고리)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| name | varchar(50) | NO | | 카테고리명 (입국 준비, 교통 가이드...) |
| icon | varchar(50) | YES | | 아이콘 코드 |
| sort_order | int | NO | 0 | 정렬 순서 |
| is_active | tinyint(1) | NO | 1 | |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 10. tips (여행 팁)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| tip_category_id | bigint FK | NO | | 팁 카테고리 |
| city_id | bigint FK | YES | | 도시 (NULL이면 전체 공통) |
| title | varchar(200) | NO | | 제목 |
| content | text | NO | | 내용 (HTML) |
| image_url | varchar(500) | YES | | 이미지 |
| sort_order | int | NO | 0 | 정렬 순서 |
| status | enum('PUBLIC','PRIVATE') | NO | 'PUBLIC' | 상태 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

### Index
- `idx_tips_category_status` (tip_category_id, status, sort_order)

---

## 11. phrase_categories (번역카드 카테고리)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| name | varchar(50) | NO | | 카테고리명 (식당, 택시, 쇼핑...) |
| icon | varchar(50) | YES | | 아이콘 코드 |
| sort_order | int | NO | 0 | 정렬 순서 |
| is_active | tinyint(1) | NO | 1 | |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 12. phrases (번역카드)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| phrase_category_id | bigint FK | NO | | 카테고리 |
| text_ko | varchar(300) | NO | | 한국어 |
| text_cn | varchar(300) | NO | | 중국어 |
| pinyin | varchar(500) | YES | | 병음 |
| sort_order | int | NO | 0 | 정렬 순서 |
| status | enum('PUBLIC','PRIVATE') | NO | 'PUBLIC' | 상태 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

### Index
- `idx_phrases_category_status` (phrase_category_id, status, sort_order)

---

## 13. banners (실시간 팁 배너)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| city_id | bigint FK | YES | | 도시 (NULL이면 전체) |
| content | varchar(300) | NO | | 배너 내용 |
| type | enum('INFO','WARNING','URGENT') | NO | 'INFO' | 배너 유형 |
| start_date | date | YES | | 시작일 |
| end_date | date | YES | | 종료일 (NULL이면 상시) |
| is_active | tinyint(1) | NO | 1 | 활성 상태 |
| sort_order | int | NO | 0 | 정렬 순서 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 14. bookmarks (북마크)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| user_id | bigint FK | NO | | 사용자 |
| place_id | bigint FK | NO | | 장소 |
| created_at | timestamp | YES | | |

### Index
- `unique_user_place` (user_id, place_id) UNIQUE

---

## 15. travel_courses (여행 코스)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| user_id | bigint FK | NO | | 사용자 |
| title | varchar(100) | NO | | 코스 제목 |
| start_date | date | YES | | 여행 시작일 |
| end_date | date | YES | | 여행 종료일 |
| memo | text | YES | | 메모 |
| created_at | timestamp | YES | | |
| updated_at | timestamp | YES | | |

---

## 16. travel_course_items (여행 코스 장소)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| travel_course_id | bigint FK | NO | | 여행 코스 |
| place_id | bigint FK | NO | | 장소 |
| day_number | int | YES | | 몇일차 (1, 2, 3...) |
| sort_order | int | NO | 0 | 일차 내 순서 |
| memo | varchar(300) | YES | | 메모 |
| created_at | timestamp | YES | | |

---

## 17. view_histories (조회 기록)

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK AI | | | |
| user_id | bigint FK | NO | | 사용자 |
| place_id | bigint FK | NO | | 장소 |
| viewed_at | timestamp | NO | | 조회 시각 |

### Index
- `idx_view_user_date` (user_id, viewed_at DESC)

---

## 변경 이력

| 날짜 | 내용 |
|------|------|
| 2026-03-25 | 초기 스키마 설계 (17개 테이블) |
