<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import maplibregl from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'
import api from '../../shared/api'
import type { Place, City, Category, ApiResponse, PaginatedResponse } from '../../shared/types/place'
import { imageUrl } from '../../shared/utils/image'

const router = useRouter()
const route = useRoute()
const mapContainer = ref<HTMLDivElement | null>(null)
let map: maplibregl.Map | null = null
const markers: maplibregl.Marker[] = []

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

// GPS
let watchId: number | null = null
const isTracking = ref(false)
let myLocationMarker: maplibregl.Marker | null = null

// MapTiler
const MAPTILER_KEY = 'rYfnlvyg9JE0z3VidiZT'

// 언어
const langOptions = [
  { code: 'ko', label: '한국어', icon: '🇰🇷' },
  { code: 'zh', label: '中文', icon: '🇨🇳' },
  { code: 'en', label: 'EN', icon: '🇺🇸' },
]
const currentLangIdx = ref(0)

// 지도 스타일
const tileStyles = [
  { name: '기본', icon: '🗺', style: 'streets-v2' },
  { name: '심플', icon: '⬜', style: 'dataviz-light' },
  { name: '다크', icon: '⬛', style: 'dataviz-dark' },
  { name: '위성', icon: '🛰', style: 'satellite' },
]
const currentTileIdx = ref(0)

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

function getStyleUrl() {
  const style = tileStyles[currentTileIdx.value].style
  const lang = langOptions[currentLangIdx.value].code
  return `https://api.maptiler.com/maps/${style}/style.json?key=${MAPTILER_KEY}&language=${lang}`
}

async function fetchFilters() {
  try {
    const [cityRes, catRes] = await Promise.all([
      api.get<ApiResponse<City[]>>('/api/user/cities'),
      api.get<ApiResponse<Category[]>>('/api/user/categories'),
    ])
    cities.value = cityRes.data.data
    categories.value = catRes.data.data

    const shanghai = cities.value.find(c => c.name_ko === '상하이')
    if (shanghai && map) {
      selectedCity.value = shanghai.id
      map.setCenter([Number(shanghai.longitude), Number(shanghai.latitude)])
      map.setZoom(13)
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

function createMarkerEl(color: string, label: string): HTMLDivElement {
  const el = document.createElement('div')
  el.innerHTML = `
    <div style="
      display: flex; flex-direction: column; align-items: center; cursor: pointer;
    ">
      <div style="
        background: ${color}; color: white;
        padding: 5px 10px; border-radius: 14px;
        font-size: 12px; font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 3px 8px rgba(0,0,0,0.35);
        border: 2px solid white;
        letter-spacing: 0.3px;
      ">${label}</div>
      <div style="
        width: 0; height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-top: 9px solid ${color};
        margin-top: -1px;
      "></div>
    </div>
  `
  return el
}

function renderMarkers() {
  // 기존 마커 제거
  markers.forEach(m => m.remove())
  markers.length = 0

  places.value.forEach(place => {
    const color = place.category?.color || '#3b82f6'
    const el = createMarkerEl(color, place.name_ko)

    el.addEventListener('click', () => {
      selectedPlace.value = place
      if (map) map.panTo([Number(place.longitude), Number(place.latitude)])
    })

    const marker = new maplibregl.Marker({ element: el, anchor: 'bottom' })
      .setLngLat([Number(place.longitude), Number(place.latitude)])
      .addTo(map!)

    markers.push(marker)
  })
}

function initMap() {
  if (!mapContainer.value) return

  map = new maplibregl.Map({
    container: mapContainer.value,
    style: getStyleUrl(),
    center: [121.4737, 31.2304], // 상하이
    zoom: 13,
  })

  map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'bottom-right')

  map.on('load', () => {
    fetchFilters().then(fetchPlaces).then(() => {
      const lat = route.query.lat ? Number(route.query.lat) : null
      const lng = route.query.lng ? Number(route.query.lng) : null
      const name = route.query.name as string | undefined
      if (lat && lng && map) {
        map.setCenter([lng, lat])
        map.setZoom(16)
        if (name) {
          const el = createMarkerEl('#ef4444', name)
          new maplibregl.Marker({ element: el, anchor: 'bottom' })
            .setLngLat([lng, lat])
            .addTo(map)
        }
      }
    })
  })
}

function cycleTileStyle() {
  currentTileIdx.value = (currentTileIdx.value + 1) % tileStyles.length
  if (map) map.setStyle(getStyleUrl())
  // 스타일 변경 후 마커 재렌더
  map?.once('styledata', () => renderMarkers())
}

function cycleLang() {
  currentLangIdx.value = (currentLangIdx.value + 1) % langOptions.length
  if (map) map.setStyle(getStyleUrl())
  map?.once('styledata', () => renderMarkers())
}

// GPS
function toggleMyLocation() {
  if (isTracking.value) { stopTracking(); return }
  if (!navigator.geolocation) { alert('위치 추적을 지원하지 않습니다.'); return }

  isTracking.value = true
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      updateMyLocation(pos.coords.latitude, pos.coords.longitude)
      if (map) { map.setCenter([pos.coords.longitude, pos.coords.latitude]); map.setZoom(15) }
    },
    () => { alert('위치를 가져올 수 없습니다.'); isTracking.value = false },
    { enableHighAccuracy: true }
  )
  watchId = navigator.geolocation.watchPosition(
    (pos) => updateMyLocation(pos.coords.latitude, pos.coords.longitude),
    () => {},
    { enableHighAccuracy: true, maximumAge: 5000 }
  )
}

function updateMyLocation(lat: number, lng: number) {
  if (!map) return
  if (myLocationMarker) {
    myLocationMarker.setLngLat([lng, lat])
  } else {
    const el = document.createElement('div')
    el.style.cssText = 'width:16px;height:16px;background:#4285F4;border:3px solid white;border-radius:50%;box-shadow:0 0 8px rgba(66,133,244,0.5);'
    myLocationMarker = new maplibregl.Marker({ element: el })
      .setLngLat([lng, lat])
      .addTo(map)
  }
}

function stopTracking() {
  isTracking.value = false
  if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null }
  if (myLocationMarker) { myLocationMarker.remove(); myLocationMarker = null }
}

// 검색
function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  const q = searchQuery.value.trim()
  if (!q) { searchResults.value = []; showSearchResults.value = false; return }
  searchTimer = setTimeout(() => doSearch(q), 300)
}

async function doSearch(q: string) {
  searching.value = true
  showSearchResults.value = true
  searchResults.value = []
  try {
    const [dbRes, nominatimRes] = await Promise.allSettled([
      api.get<ApiResponse<PaginatedResponse<Place>>>('/api/user/places', { params: { search: q, per_page: 5 } }),
      fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=5&accept-language=ko,zh&countrycodes=cn&addressdetails=1`, {
        headers: { 'User-Agent': 'EasyChina/1.0' },
      }).then(r => r.json()),
    ])
    const results: SearchResult[] = []
    if (dbRes.status === 'fulfilled') {
      dbRes.value.data.data.data.forEach((p: Place) => {
        results.push({ type: 'db', name: p.name_ko, nameKo: p.name_ko, nameCn: p.name_cn, lat: Number(p.latitude), lng: Number(p.longitude), placeId: p.id, category: p.category?.name_ko })
      })
    }
    if (nominatimRes.status === 'fulfilled') {
      (nominatimRes.value as any[]).forEach((r: any) => {
        const isDup = results.some(e => Math.abs(e.lat - parseFloat(r.lat)) < 0.001 && Math.abs(e.lng - parseFloat(r.lon)) < 0.001)
        if (!isDup) results.push({ type: 'nominatim', name: r.display_name?.split(',')[0] || r.name, lat: parseFloat(r.lat), lng: parseFloat(r.lon), address: r.display_name })
      })
    }
    searchResults.value = results
  } catch (e) { console.error('Search error:', e) }
  finally { searching.value = false }
}

function selectSearchResult(result: SearchResult) {
  showSearchResults.value = false
  searchQuery.value = result.name
  if (map) { map.setCenter([result.lng, result.lat]); map.setZoom(16) }
  const color = result.type === 'db' ? '#3b82f6' : '#ef4444'
  const el = createMarkerEl(color, result.name)
  if (result.type === 'db' && result.placeId) {
    el.addEventListener('click', () => { const p = places.value.find(p => p.id === result.placeId); if (p) selectedPlace.value = p })
  }
  const m = new maplibregl.Marker({ element: el, anchor: 'bottom' }).setLngLat([result.lng, result.lat]).addTo(map!)
  markers.push(m)
}

function closeSearchResults() { showSearchResults.value = false }

function selectCategory(id: number | null) {
  selectedCategory.value = selectedCategory.value === id ? null : id
  fetchPlaces()
}

function onCityChange() {
  const city = cities.value.find(c => c.id === selectedCity.value)
  if (city && map) { map.setCenter([Number(city.longitude), Number(city.latitude)]); map.setZoom(13) }
  fetchPlaces()
}

function goToDetail(placeId: number) { router.push(`/places/${placeId}`) }
function closeBottomSheet() { selectedPlace.value = null }
function copyAddress(address: string) { navigator.clipboard.writeText(address) }

watch(selectedCity, onCityChange)

onMounted(initMap)
onUnmounted(() => {
  stopTracking()
  if (map) { map.remove(); map = null }
})
</script>

<template>
  <div class="relative h-[calc(100vh-56px)]">
    <!-- Search Bar -->
    <div class="absolute top-3 left-3 right-3 z-[10] flex gap-2">
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
        <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name_ko }}</option>
      </select>
    </div>

    <!-- Search Results Dropdown -->
    <div v-if="showSearchResults" class="absolute top-14 left-3 right-16 z-[20]">
      <div class="fixed inset-0" @click="closeSearchResults"></div>
      <div class="relative bg-white rounded-xl shadow-lg max-h-72 overflow-y-auto overscroll-contain" @click.stop>
        <div v-if="searching" class="p-4 text-center text-xs text-gray-400">검색 중...</div>
        <div v-else-if="searchResults.length === 0" class="p-4 text-center text-xs text-gray-400">결과 없음</div>
        <template v-else>
          <button
            v-for="(result, idx) in searchResults" :key="idx"
            @click="selectSearchResult(result)"
            class="w-full text-left px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 active:bg-gray-100"
          >
            <div class="flex items-center gap-2">
              <span class="text-xs px-1.5 py-0.5 rounded-full shrink-0" :class="result.type === 'db' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500'">
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
    <div v-if="!showSearchResults" class="absolute top-16 left-3 right-3 z-[10] flex gap-2 overflow-x-auto scrollbar-hide">
      <button
        @click="selectCategory(null)"
        class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium shadow-md transition-colors"
        :class="!selectedCategory ? 'bg-blue-500 text-white' : 'bg-white text-gray-600'"
      >전체</button>
      <button
        v-for="cat in categories" :key="cat.id"
        @click="selectCategory(cat.id)"
        class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium shadow-md transition-colors"
        :class="selectedCategory === cat.id ? 'text-white' : 'bg-white text-gray-600'"
        :style="selectedCategory === cat.id ? { backgroundColor: cat.color || '#3b82f6' } : {}"
      >{{ cat.name_ko }}</button>
    </div>

    <!-- Map -->
    <div ref="mapContainer" class="w-full h-full"></div>

    <!-- Loading -->
    <div v-if="loading" class="absolute top-28 left-1/2 -translate-x-1/2 z-[10]">
      <span class="bg-white shadow-lg rounded-full px-4 py-2 text-xs text-gray-500">불러오는 중...</span>
    </div>

    <!-- Language Toggle -->
    <button
      @click="cycleLang"
      class="absolute bottom-40 right-3 z-[10] w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center active:bg-gray-100"
    >
      <span class="text-sm">{{ langOptions[currentLangIdx].icon }}</span>
    </button>

    <!-- Map Style -->
    <button
      @click="cycleTileStyle"
      class="absolute bottom-28 right-3 z-[10] w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center active:bg-gray-100"
    >
      <span class="text-lg">{{ tileStyles[currentTileIdx].icon }}</span>
    </button>

    <!-- My Location -->
    <button
      @click="toggleMyLocation"
      class="absolute bottom-16 right-3 z-[10] w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center active:bg-gray-100"
      :class="isTracking ? 'ring-2 ring-blue-400' : ''"
    >
      <span class="text-lg">{{ isTracking ? '📍' : '🎯' }}</span>
    </button>

    <!-- Place count -->
    <div class="absolute bottom-4 left-3 z-[10]">
      <span class="bg-white/90 shadow rounded-full px-3 py-1.5 text-xs text-gray-500">📍 {{ places.length }}개 장소</span>
    </div>

    <!-- Bottom Sheet -->
    <div
      v-if="selectedPlace"
      class="absolute bottom-0 left-0 right-0 z-[10] bg-white rounded-t-2xl shadow-2xl p-4 transition-transform"
      @click.self="closeBottomSheet"
    >
      <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-3" @click="closeBottomSheet"></div>
      <div class="flex gap-3">
        <div class="w-20 h-20 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
          <img
            v-if="imageUrl((selectedPlace as any).primary_image?.image_url || selectedPlace.images?.[0]?.image_url)"
            :src="imageUrl((selectedPlace as any).primary_image?.image_url || selectedPlace.images?.[0]?.image_url)!"
            class="w-full h-full object-cover" loading="lazy"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-2xl text-gray-300">📷</div>
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-bold text-gray-900 truncate">{{ selectedPlace.name_ko }}</h3>
          <p class="text-xs text-gray-400 truncate">{{ selectedPlace.name_cn }}</p>
          <div class="flex items-center gap-2 mt-1">
            <span v-if="selectedPlace.rating" class="text-xs text-yellow-500">⭐ {{ selectedPlace.rating }}</span>
            <span v-if="selectedPlace.category" class="text-[10px] text-white px-1.5 py-0.5 rounded-full" :style="{ backgroundColor: selectedPlace.category.color || '#3b82f6' }">{{ selectedPlace.category.name_ko }}</span>
          </div>
          <p v-if="selectedPlace.address_cn" class="text-xs text-gray-400 mt-1 truncate">{{ selectedPlace.address_ko || selectedPlace.address_cn }}</p>
        </div>
      </div>
      <div class="flex gap-2 mt-3">
        <button @click="copyAddress(selectedPlace!.address_cn)" class="flex-1 py-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 active:bg-gray-200">📋 주소 복사</button>
        <button @click="goToDetail(selectedPlace!.id)" class="flex-1 py-2 rounded-lg text-xs font-medium bg-blue-500 text-white active:bg-blue-600">상세 보기 →</button>
      </div>
    </div>
  </div>
</template>
