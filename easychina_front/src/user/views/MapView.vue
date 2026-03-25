<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import api from '../../shared/api'
import type { Place, City, Category, ApiResponse, PaginatedResponse } from '../../shared/types/place'

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

// 카테고리 색상 → Leaflet 마커 아이콘
function createMarkerIcon(color: string, label: string) {
  return L.divIcon({
    className: 'custom-marker',
    html: `<div style="
      background: ${color};
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      border: 2px solid white;
      text-align: center;
      max-width: 120px;
      overflow: hidden;
      text-overflow: ellipsis;
    ">${label}</div>`,
    iconSize: [0, 0],
    iconAnchor: [0, 0],
  })
}

async function fetchFilters() {
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

function initMap() {
  if (!mapContainer.value) return

  map = L.map(mapContainer.value, {
    center: [31.2304, 121.4737], // 상하이 기본
    zoom: 13,
    zoomControl: false,
  })

  // 타일 레이어 (OSM - API Key 불필요)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  }).addTo(map)

  // 줌 컨트롤 우하단
  L.control.zoom({ position: 'bottomright' }).addTo(map)

  markersLayer.addTo(map)
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
          @keyup.enter="fetchPlaces()"
          type="text"
          placeholder="장소 검색 (한국어/중국어)"
          class="w-full bg-white shadow-lg rounded-xl px-4 py-2.5 text-sm pl-9 border-0 outline-none"
        />
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
      </div>
      <select
        v-model="selectedCity"
        class="bg-white shadow-lg rounded-xl px-3 py-2.5 text-sm border-0 outline-none"
      >
        <option :value="null">전체</option>
        <option v-for="city in cities" :key="city.id" :value="city.id">
          {{ city.name_ko }}
        </option>
      </select>
    </div>

    <!-- Category Chips -->
    <div class="absolute top-16 left-3 right-3 z-[1000] flex gap-2 overflow-x-auto scrollbar-hide">
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
            v-if="selectedPlace.images?.[0]"
            :src="selectedPlace.images[0].image_url"
            class="w-full h-full object-cover"
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
}

/* 지도 위 검색바 아래로 안 가리게 */
.leaflet-top {
  top: 100px !important;
}
</style>
