<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import api from '../../shared/api'
import type { Place, City, Category, ApiResponse, PaginatedResponse } from '../../shared/types/place'
import PlaceCard from '../components/PlaceCard.vue'
import { isOffline, getCachedPlaces } from '../../shared/utils/offline'

const cities = ref<City[]>([])
const categories = ref<Category[]>([])
const places = ref<Place[]>([])
const selectedCity = ref<number | null>(null)
const selectedCategory = ref<number | null>(null)
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const totalCount = ref(0)
const searchQuery = ref('')
const offlineMode = ref(false)

async function fetchFilters() {
  try {
    const [cityRes, catRes] = await Promise.all([
      api.get<ApiResponse<City[]>>('/api/user/cities'),
      api.get<ApiResponse<Category[]>>('/api/user/categories'),
    ])
    cities.value = cityRes.data.data
    categories.value = catRes.data.data
  } catch {
    // 오프라인: 기본 도시/카테고리
    offlineMode.value = true
  }
}

async function fetchPlaces(page = 1) {
  if (loading.value) return
  loading.value = true
  try {
    if (isOffline() || offlineMode.value) {
      await fetchPlacesOffline()
      return
    }
    const { data } = await api.get<ApiResponse<PaginatedResponse<Place>>>('/api/user/places', {
      params: {
        city_id: selectedCity.value,
        category_id: selectedCategory.value,
        search: searchQuery.value || undefined,
        page,
      },
    })
    const result = data.data
    places.value = result.data
    currentPage.value = result.current_page
    lastPage.value = result.last_page
    totalCount.value = result.total
  } catch {
    // API 실패 시 오프라인 폴백
    offlineMode.value = true
    await fetchPlacesOffline()
  } finally {
    loading.value = false
  }
}

async function fetchPlacesOffline() {
  try {
    // 모든 도시 캐시 합치기 (city_id 2=상하이 등)
    let cached: Place[] = []
    for (const cityId of [1,2,3,4,5,6,7,8]) {
      const cityPlaces = await getCachedPlaces(cityId)
      cached.push(...cityPlaces)
    }

    // 필터 적용
    if (selectedCity.value) {
      cached = cached.filter(p => p.city_id === selectedCity.value)
    }
    if (selectedCategory.value) {
      cached = cached.filter(p => p.category_id === selectedCategory.value)
    }
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      cached = cached.filter(p =>
        p.name_ko.toLowerCase().includes(q) ||
        p.name_cn.toLowerCase().includes(q)
      )
    }

    // 클라이언트 페이징
    const perPage = 6
    const start = (currentPage.value - 1) * perPage
    totalCount.value = cached.length
    lastPage.value = Math.max(1, Math.ceil(cached.length / perPage))
    places.value = cached.slice(start, start + perPage)
  } catch {
    places.value = []
  } finally {
    loading.value = false
  }
}

function goToPage(page: number) {
  if (page < 1 || page > lastPage.value || page === currentPage.value) return
  fetchPlaces(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// 페이지 번호 목록 생성 (최대 5개)
const pageNumbers = computed(() => {
  const pages: number[] = []
  const total = lastPage.value
  const current = currentPage.value

  let start = Math.max(1, current - 2)
  let end = Math.min(total, start + 4)
  start = Math.max(1, end - 4)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

function selectCategory(id: number | null) {
  selectedCategory.value = selectedCategory.value === id ? null : id
  fetchPlaces(1)
}

watch(selectedCity, () => fetchPlaces(1))

onMounted(() => {
  fetchFilters()
  fetchPlaces()
})
</script>

<template>
  <div class="px-4 pt-4 pb-20">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <h1 class="text-xl font-bold">여행지</h1>
      <select
        v-model="selectedCity"
        class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 bg-white"
      >
        <option :value="null">전체 도시</option>
        <option v-for="city in cities" :key="city.id" :value="city.id">
          {{ city.name_ko }}
        </option>
      </select>
    </div>

    <!-- Search -->
    <div class="relative mb-3">
      <input
        v-model="searchQuery"
        @keyup.enter="fetchPlaces(1)"
        type="text"
        placeholder="장소명 검색 (한국어/중국어)"
        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm pl-10"
      />
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
    </div>

    <!-- Category Chips -->
    <div class="flex gap-2 overflow-x-auto pb-3 scrollbar-hide">
      <button
        @click="selectCategory(null)"
        class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
        :class="!selectedCategory ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
      >
        전체
      </button>
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="selectCategory(cat.id)"
        class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
        :class="selectedCategory === cat.id ? 'text-white' : 'bg-gray-100 text-gray-600'"
        :style="selectedCategory === cat.id ? { backgroundColor: cat.color || '#3b82f6' } : {}"
      >
        {{ cat.name_ko }}
      </button>
    </div>

    <!-- 결과 수 -->
    <p v-if="totalCount > 0" class="text-xs text-gray-400 mb-2">{{ totalCount }}개의 장소</p>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Place Grid -->
    <template v-else>
      <div class="grid grid-cols-2 gap-3">
        <PlaceCard
          v-for="place in places"
          :key="place.id"
          :place="place"
        />
      </div>

      <!-- Empty -->
      <div v-if="places.length === 0" class="text-center py-12">
        <div class="text-4xl mb-2">🏙</div>
        <p class="text-gray-400 text-sm">등록된 장소가 없습니다</p>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex justify-center items-center gap-1 mt-6">
        <!-- Prev -->
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="w-9 h-9 rounded-lg flex items-center justify-center text-sm"
          :class="currentPage === 1 ? 'text-gray-300' : 'text-gray-600 active:bg-gray-100'"
        >
          ‹
        </button>

        <!-- Page Numbers -->
        <button
          v-for="p in pageNumbers"
          :key="p"
          @click="goToPage(p)"
          class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-medium transition-colors"
          :class="p === currentPage
            ? 'bg-blue-500 text-white'
            : 'text-gray-600 active:bg-gray-100'"
        >
          {{ p }}
        </button>

        <!-- Next -->
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === lastPage"
          class="w-9 h-9 rounded-lg flex items-center justify-center text-sm"
          :class="currentPage === lastPage ? 'text-gray-300' : 'text-gray-600 active:bg-gray-100'"
        >
          ›
        </button>
      </div>
    </template>
  </div>
</template>
