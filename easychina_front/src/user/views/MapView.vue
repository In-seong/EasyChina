<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import api from '../../shared/api'
import type { Place, City, Category, ApiResponse, PaginatedResponse } from '../../shared/types/place'
import { imageUrl } from '../../shared/utils/image'

const router = useRouter()
const route = useRoute()
const mapContainer = ref<HTMLDivElement | null>(null)
let map: L.Map | null = null
const markersLayer = L.layerGroup()

const cities = ref<City[]>([])
const categories = ref<Category[]>([])
const places = ref<Place[]>([])
const selectedCity = ref<number | null>(null)
const selectedCategory = ref<number | null>(null)
const searchQuery = ref('')
const selectedPlace = ref<Place | null>(null)
const loading = ref(false)
const searchResults = ref<SearchResult[]>([])
const showSearchResults = ref(false)
const searching = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | null = null

interface SearchResult {
  type: 'db' | 'nominatim'
  name: string
  nameKo?: string
  nameCn?: string
  lat: number
  lng: number
  address?: string
  placeId?: number
  category?: string
}

// 카테고리 색상 → Leaflet 마커 아이콘
function createMarkerIcon(color: string, label: string) {
  return L.divIcon({
    className: 'custom-marker',
    html: `
      <div class="marker-pin" style="
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
      ">
        <div style="
          background: ${color};
          color: white;
          padding: 6px 12px;
          border-radius: 16px;
          font-size: 13px;
          font-weight: 700;
          white-space: nowrap;
          box-shadow: 0 3px 8px rgba(0,0,0,0.35);
          border: 2.5px solid white;
          text-align: center;
          letter-spacing: 0.3px;
        ">${label}</div>
        <div style="
          width: 0; height: 0;
          border-left: 8px solid transparent;
          border-right: 8px solid transparent;
          border-top: 10px solid ${color};
          margin-top: -2px;
          filter: drop-shadow(0 2px 2px rgba(0,0,0,0.2));
        "></div>
      </div>
    `,
    iconSize: [0, 0],
    iconAnchor: [0, 42],
  })
}

async function fetchFilters() {
  try {
    const [cityRes, catRes] = await Promise.all([
      api.get<ApiResponse<City[]>>('/api/user/cities'),
      api.get<ApiResponse<Category[]>>('/api/user/categories'),
    ])
    cities.value = cityRes.data.data
    categories.value = catRes.data.data

    // 기본: 상하이
    const shanghai = cities.value.find(c => c.name_ko === '상하이')
    if (shanghai) {
      selectedCity.value = shanghai.id
      if (map) {
        map.setView([Number(shanghai.latitude), Number(shanghai.longitude)], 13)
      }
    }
  } catch (e) {
    console.error('Failed to fetch filters:', e)
  }
}

async function fetchPlaces() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<PaginatedResponse<Place>>>('/api/user/places', {
      params: {
        city_id: selectedCity.value,
        category_id: selectedCategory.value,
        search: searchQuery.value || undefined,
        per_page: 100,
      },
    })
    places.value = data.data.data
    renderMarkers()
  } catch (e) {
    console.error('Failed to fetch places:', e)
  } finally {
    loading.value = false
  }
}

function renderMarkers() {
  markersLayer.clearLayers()

  places.value.forEach(place => {
    const color = place.category?.color || '#3b82f6'
    const icon = createMarkerIcon(color, place.name_ko)

    const marker = L.marker(
      [Number(place.latitude), Number(place.longitude)],
      { icon }
    )

    marker.on('click', () => {
      selectedPlace.value = place
      if (map) {
        map.panTo([Number(place.latitude), Number(place.longitude)])
      }
    })

    markersLayer.addLayer(marker)
  })
}

let myLocationMarker: L.Marker | null = null
let myLocationCircle: L.Circle | null = null
let watchId: number | null = null
const isTracking = ref(false)

// 지도 타일 스타일
const tileStyles = [
  { name: '기본', icon: '🗺', url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', attr: '&copy; CartoDB' },
  { name: '심플', icon: '⬜', url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', attr: '&copy; CartoDB' },
  { name: '다크', icon: '⬛', url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', attr: '&copy; CartoDB' },
  { name: '위성', icon: '🛰', url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', attr: '&copy; Esri' },
]
const currentTileIdx = ref(0)
let currentTileLayer: L.TileLayer | null = null

function initMap() {
  if (!mapContainer.value) return

  map = L.map(mapContainer.value, {
    center: [31.2304, 121.4737],
    zoom: 13,
    zoomControl: false,
  })

  // 기본 타일: CartoDB Voyager (깔끔)
  setTileStyle(0)

  // 줌 컨트롤 우하단
  L.control.zoom({ position: 'bottomright' }).addTo(map)

  markersLayer.addTo(map)
}

function setTileStyle(idx: number) {
  if (!map) return
  currentTileIdx.value = idx
  const style = tileStyles[idx]

  if (currentTileLayer) {
    map.removeLayer(currentTileLayer)
  }

  currentTileLayer = L.tileLayer(style.url, {
    attribution: style.attr,
    maxZoom: 19,
  }).addTo(map)
}

function cycleTileStyle() {
  const next = (currentTileIdx.value + 1) % tileStyles.length
  setTileStyle(next)
}

function toggleMyLocation() {
  if (isTracking.value) {
    stopTracking()
    return
  }

  if (!navigator.geolocation) {
    alert('이 브라우저에서는 위치 추적을 지원하지 않습니다.')
    return
  }

  isTracking.value = true

  // 현재 위치 1회 가져오기
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      updateMyLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy)
      if (map) map.setView([pos.coords.latitude, pos.coords.longitude], 15)
    },
    (err) => {
      console.error('위치 오류:', err)
      alert('위치를 가져올 수 없습니다. 위치 권한을 허용해주세요.')
      isTracking.value = false
    },
    { enableHighAccuracy: true }
  )

  // 실시간 추적
  watchId = navigator.geolocation.watchPosition(
    (pos) => {
      updateMyLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy)
    },
    () => {},
    { enableHighAccuracy: true, maximumAge: 5000 }
  )
}

function updateMyLocation(lat: number, lng: number, accuracy: number) {
  if (!map) return

  if (myLocationMarker) {
    myLocationMarker.setLatLng([lat, lng])
    myLocationCircle?.setLatLng([lat, lng])
    myLocationCircle?.setRadius(accuracy)
  } else {
    // 파란 점 마커
    myLocationMarker = L.marker([lat, lng], {
      icon: L.divIcon({
        className: 'my-location-marker',
        html: `<div style="
          width: 16px; height: 16px;
          background: #4285F4;
          border: 3px solid white;
          border-radius: 50%;
          box-shadow: 0 0 8px rgba(66,133,244,0.5);
        "></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8],
      }),
      zIndexOffset: 1000,
    }).addTo(map)

    // 정확도 원
    myLocationCircle = L.circle([lat, lng], {
      radius: accuracy,
      color: '#4285F4',
      fillColor: '#4285F4',
      fillOpacity: 0.1,
      weight: 1,
    }).addTo(map)
  }
}

function stopTracking() {
  isTracking.value = false
  if (watchId !== null) {
    navigator.geolocation.clearWatch(watchId)
    watchId = null
  }
  if (myLocationMarker && map) {
    map.removeLayer(myLocationMarker)
    myLocationMarker = null
  }
  if (myLocationCircle && map) {
    map.removeLayer(myLocationCircle)
    myLocationCircle = null
  }
}

// 검색 (디바운스)
function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  const q = searchQuery.value.trim()
  if (!q) {
    searchResults.value = []
    showSearchResults.value = false
    return
  }
  searchTimer = setTimeout(() => doSearch(q), 300)
}

async function doSearch(q: string) {
  searching.value = true
  showSearchResults.value = true
  searchResults.value = []

  try {
    // DB 검색 + Nominatim 병렬
    const [dbRes, nominatimRes] = await Promise.allSettled([
      api.get<ApiResponse<PaginatedResponse<Place>>>('/api/user/places', {
        params: { search: q, per_page: 5 },
      }),
      fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=5&accept-language=ko,zh&countrycodes=cn&addressdetails=1`, {
        headers: { 'User-Agent': 'EasyChina/1.0' },
      }).then(r => r.json()),
    ])

    const results: SearchResult[] = []

    // DB 결과
    if (dbRes.status === 'fulfilled') {
      const dbPlaces = dbRes.value.data.data.data
      dbPlaces.forEach((p: Place) => {
        results.push({
          type: 'db',
          name: p.name_ko,
          nameKo: p.name_ko,
          nameCn: p.name_cn,
          lat: Number(p.latitude),
          lng: Number(p.longitude),
          placeId: p.id,
          category: p.category?.name_ko,
        })
      })
    }

    // Nominatim 결과
    if (nominatimRes.status === 'fulfilled') {
      const nomResults = nominatimRes.value as any[]
      nomResults.forEach((r: any) => {
        // DB 결과와 중복 제거 (근접 좌표)
        const isDuplicate = results.some(
          existing => Math.abs(existing.lat - parseFloat(r.lat)) < 0.001
                   && Math.abs(existing.lng - parseFloat(r.lon)) < 0.001
        )
        if (!isDuplicate) {
          results.push({
            type: 'nominatim',
            name: r.display_name?.split(',')[0] || r.name,
            lat: parseFloat(r.lat),
            lng: parseFloat(r.lon),
            address: r.display_name,
          })
        }
      })
    }

    searchResults.value = results
  } catch (e) {
    console.error('Search error:', e)
  } finally {
    searching.value = false
  }
}

function selectSearchResult(result: SearchResult) {
  showSearchResults.value = false
  searchQuery.value = result.name

  if (map) {
    map.setView([result.lat, result.lng], 16)
  }

  // 검색 결과 마커 표시
  const color = result.type === 'db' ? '#3b82f6' : '#ef4444'
  const icon = createMarkerIcon(color, result.name)
  const marker = L.marker([result.lat, result.lng], { icon })

  if (result.type === 'db' && result.placeId) {
    marker.on('click', () => {
      const dbPlace = places.value.find(p => p.id === result.placeId)
      if (dbPlace) selectedPlace.value = dbPlace
    })
  }

  markersLayer.addLayer(marker)
}

function closeSearchResults() {
  showSearchResults.value = false
}

function selectCategory(id: number | null) {
  selectedCategory.value = selectedCategory.value === id ? null : id
  fetchPlaces()
}

function onCityChange() {
  const city = cities.value.find(c => c.id === selectedCity.value)
  if (city && map) {
    map.setView([Number(city.latitude), Number(city.longitude)], 13)
  }
  fetchPlaces()
}

function goToDetail(placeId: number) {
  router.push(`/places/${placeId}`)
}

function closeBottomSheet() {
  selectedPlace.value = null
}

function copyAddress(address: string) {
  navigator.clipboard.writeText(address)
}

watch(selectedCity, onCityChange)

onMounted(() => {
  initMap()
  fetchFilters().then(fetchPlaces).then(() => {
    // URL 파라미터로 특정 위치 이동 (/map?lat=...&lng=...&name=...)
    const lat = route.query.lat ? Number(route.query.lat) : null
    const lng = route.query.lng ? Number(route.query.lng) : null
    const name = route.query.name as string | undefined
    if (lat && lng && map) {
      map.setView([lat, lng], 16)
      if (name) {
        const icon = createMarkerIcon('#ef4444', name)
        L.marker([lat, lng], { icon }).addTo(markersLayer)
      }
    }
  })
})

onUnmounted(() => {
  stopTracking()
  if (map) {
    map.remove()
    map = null
  }
})
</script>

<template>
  <div class="relative h-[calc(100vh-56px)]">
    <!-- Search Bar -->
    <div class="absolute top-3 left-3 right-3 z-[1000] flex gap-2">
      <div class="flex-1 relative">
        <input
          v-model="searchQuery"
          @input="onSearchInput"
          @keyup.enter="fetchPlaces()"
          @focus="searchQuery.trim() && searchResults.length && (showSearchResults = true)"
          type="text"
          placeholder="장소, 주소 검색 (한국어/중국어/영어)"
          class="w-full bg-white shadow-lg rounded-xl px-4 py-2.5 text-sm pl-9 border-0 outline-none"
        />
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        <button
          v-if="searchQuery"
          @click="searchQuery = ''; searchResults = []; showSearchResults = false; fetchPlaces()"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
        >✕</button>
      </div>
      <select
        v-model="selectedCity"
        class="bg-white shadow-lg rounded-xl px-3 py-2.5 text-sm border-0 outline-none shrink-0"
      >
        <option :value="null">전체</option>
        <option v-for="city in cities" :key="city.id" :value="city.id">
          {{ city.name_ko }}
        </option>
      </select>
    </div>

    <!-- Search Results Dropdown (부모 밖으로 분리) -->
    <div
      v-if="showSearchResults"
      class="absolute top-14 left-3 right-16 z-[1100]"
    >
      <!-- 배경 클릭으로 닫기 -->
      <div class="fixed inset-0" @click="closeSearchResults"></div>
      <div
        class="relative bg-white rounded-xl shadow-lg max-h-72 overflow-y-auto overscroll-contain"
        @click.stop
      >
        <div v-if="searching" class="p-4 text-center text-xs text-gray-400">검색 중...</div>
        <div v-else-if="searchResults.length === 0" class="p-4 text-center text-xs text-gray-400">결과 없음</div>
        <template v-else>
          <button
            v-for="(result, idx) in searchResults"
            :key="idx"
            @click="selectSearchResult(result)"
            class="w-full text-left px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 active:bg-gray-100"
          >
            <div class="flex items-center gap-2">
              <span class="text-xs px-1.5 py-0.5 rounded-full shrink-0"
                    :class="result.type === 'db' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500'">
                {{ result.type === 'db' ? (result.category || '등록') : '지도' }}
              </span>
              <span class="text-sm font-medium text-gray-800 truncate">{{ result.name }}</span>
            </div>
            <p v-if="result.nameCn" class="text-xs text-gray-400 mt-0.5 ml-12">{{ result.nameCn }}</p>
            <p v-else-if="result.address" class="text-xs text-gray-400 mt-0.5 ml-12 truncate">{{ result.address }}</p>
          </button>
        </template>
      </div>
    </div>

    <!-- Category Chips -->
    <div v-if="!showSearchResults" class="absolute top-16 left-3 right-3 z-[1000] flex gap-2 overflow-x-auto scrollbar-hide">
      <button
        @click="selectCategory(null)"
        class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium shadow-md transition-colors"
        :class="!selectedCategory ? 'bg-blue-500 text-white' : 'bg-white text-gray-600'"
      >
        전체
      </button>
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="selectCategory(cat.id)"
        class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium shadow-md transition-colors"
        :class="selectedCategory === cat.id ? 'text-white' : 'bg-white text-gray-600'"
        :style="selectedCategory === cat.id ? { backgroundColor: cat.color || '#3b82f6' } : {}"
      >
        {{ cat.name_ko }}
      </button>
    </div>

    <!-- Map -->
    <div ref="mapContainer" class="w-full h-full"></div>

    <!-- Loading -->
    <div v-if="loading" class="absolute top-28 left-1/2 -translate-x-1/2 z-[1000]">
      <span class="bg-white shadow-lg rounded-full px-4 py-2 text-xs text-gray-500">불러오는 중...</span>
    </div>

    <!-- Map Style Button -->
    <button
      @click="cycleTileStyle"
      class="absolute bottom-28 right-3 z-[1000] w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center active:bg-gray-100"
      :title="'지도: ' + tileStyles[currentTileIdx].name"
    >
      <span class="text-lg">{{ tileStyles[currentTileIdx].icon }}</span>
    </button>

    <!-- My Location Button -->
    <button
      @click="toggleMyLocation"
      class="absolute bottom-16 right-3 z-[1000] w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center active:bg-gray-100"
      :class="isTracking ? 'ring-2 ring-blue-400' : ''"
    >
      <span class="text-lg">{{ isTracking ? '📍' : '🎯' }}</span>
    </button>

    <!-- Place count -->
    <div class="absolute bottom-4 left-3 z-[1000]">
      <span class="bg-white/90 shadow rounded-full px-3 py-1.5 text-xs text-gray-500">
        📍 {{ places.length }}개 장소
      </span>
    </div>

    <!-- Bottom Sheet (장소 선택 시) -->
    <div
      v-if="selectedPlace"
      class="absolute bottom-0 left-0 right-0 z-[1000] bg-white rounded-t-2xl shadow-2xl p-4 transition-transform"
      @click.self="closeBottomSheet"
    >
      <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-3" @click="closeBottomSheet"></div>

      <div class="flex gap-3">
        <!-- 이미지 -->
        <div class="w-20 h-20 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
          <img
            v-if="imageUrl((selectedPlace as any).primary_image?.image_url || selectedPlace.images?.[0]?.image_url)"
            :src="imageUrl((selectedPlace as any).primary_image?.image_url || selectedPlace.images?.[0]?.image_url)!"
            class="w-full h-full object-cover"
            loading="lazy"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-2xl text-gray-300">📷</div>
        </div>

        <!-- 정보 -->
        <div class="flex-1 min-w-0">
          <h3 class="font-bold text-gray-900 truncate">{{ selectedPlace.name_ko }}</h3>
          <p class="text-xs text-gray-400 truncate">{{ selectedPlace.name_cn }}</p>
          <div class="flex items-center gap-2 mt-1">
            <span v-if="selectedPlace.rating" class="text-xs text-yellow-500">⭐ {{ selectedPlace.rating }}</span>
            <span
              v-if="selectedPlace.category"
              class="text-[10px] text-white px-1.5 py-0.5 rounded-full"
              :style="{ backgroundColor: selectedPlace.category.color || '#3b82f6' }"
            >
              {{ selectedPlace.category.name_ko }}
            </span>
          </div>
          <p v-if="selectedPlace.address_cn" class="text-xs text-gray-400 mt-1 truncate">
            {{ selectedPlace.address_ko || selectedPlace.address_cn }}
          </p>
        </div>
      </div>

      <!-- 액션 버튼 -->
      <div class="flex gap-2 mt-3">
        <button
          @click="copyAddress(selectedPlace!.address_cn)"
          class="flex-1 py-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 active:bg-gray-200"
        >
          📋 주소 복사
        </button>
        <button
          @click="goToDetail(selectedPlace!.id)"
          class="flex-1 py-2 rounded-lg text-xs font-medium bg-blue-500 text-white active:bg-blue-600"
        >
          상세 보기 →
        </button>
      </div>
    </div>
  </div>
</template>

<style>
/* Leaflet 마커 스타일 오버라이드 */
.custom-marker {
  background: transparent !important;
  border: none !important;
  overflow: visible !important;
  width: auto !important;
  height: auto !important;
}

/* 마커가 다른 요소에 가려지지 않도록 */
.leaflet-marker-icon.custom-marker {
  z-index: 500 !important;
}

/* 지도 위 검색바 아래로 안 가리게 */
.leaflet-top {
  top: 100px !important;
}
</style>
