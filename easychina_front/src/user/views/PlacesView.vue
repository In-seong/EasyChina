<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import api from '../../shared/api'
import type { Place, City, Category, ApiResponse, PaginatedResponse } from '../../shared/types/place'
import PlaceCard from '../components/PlaceCard.vue'

const cities = ref<City[]>([])
const categories = ref<Category[]>([])
const places = ref<Place[]>([])
const selectedCity = ref<number | null>(null)
const selectedCategory = ref<number | null>(null)
const loading = ref(false)
const page = ref(1)
const hasMore = ref(true)
const searchQuery = ref('')
const scrollContainer = ref<HTMLDivElement | null>(null)
const totalCount = ref(0)

async function fetchFilters() {
  const [cityRes, catRes] = await Promise.all([
    api.get<ApiResponse<City[]>>('/api/user/cities'),
    api.get<ApiResponse<Category[]>>('/api/user/categories'),
  ])
  cities.value = cityRes.data.data
  categories.value = catRes.data.data
}

async function fetchPlaces(reset = false) {
  if (loading.value) return
  if (reset) {
    page.value = 1
    places.value = []
    hasMore.value = true
    totalCount.value = 0
  }
  if (!hasMore.value) return

  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<PaginatedResponse<Place>>>('/api/user/places', {
      params: {
        city_id: selectedCity.value,
        category_id: selectedCategory.value,
        search: searchQuery.value || undefined,
        page: page.value,
      },
    })
    const result = data.data
    places.value.push(...result.data)
    totalCount.value = result.total
    hasMore.value = result.current_page < result.last_page
    page.value++
  } finally {
    loading.value = false
  }
}

function selectCategory(id: number | null) {
  selectedCategory.value = selectedCategory.value === id ? null : id
  fetchPlaces(true)
}

// 무한 스크롤
function onScroll() {
  if (!scrollContainer.value || loading.value || !hasMore.value) return
  const el = scrollContainer.value
  const bottomReached = el.scrollHeight - el.scrollTop - el.clientHeight < 200
  if (bottomReached) {
    fetchPlaces()
  }
}

watch(selectedCity, () => fetchPlaces(true))

onMounted(() => {
  fetchFilters()
  fetchPlaces()
})
</script>

<template>
  <div ref="scrollContainer" @scroll="onScroll" class="h-[calc(100vh-56px)] overflow-y-auto">
    <div class="px-4 pt-4">
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
          @keyup.enter="fetchPlaces(true)"
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

      <!-- Place Grid -->
      <div class="grid grid-cols-2 gap-3 pb-4">
        <PlaceCard
          v-for="place in places"
          :key="place.id"
          :place="place"
        />
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="flex justify-center py-6">
        <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- End of list -->
      <div v-else-if="!hasMore && places.length > 0" class="text-center py-4 pb-8">
        <p class="text-xs text-gray-300">모든 장소를 불러왔습니다</p>
      </div>

      <!-- Empty -->
      <div v-else-if="!loading && places.length === 0" class="text-center py-12">
        <div class="text-4xl mb-2">🏙</div>
        <p class="text-gray-400 text-sm">등록된 장소가 없습니다</p>
      </div>
    </div>
  </div>
</template>
