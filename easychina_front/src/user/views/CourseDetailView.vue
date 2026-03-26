<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../shared/api'
import type { TravelCourse } from '../../shared/types/place'
import { imageUrl } from '../../shared/utils/image'
import { startAMapNavigation } from '../../shared/utils/navigation'

const route = useRoute()
const router = useRouter()
const course = ref<TravelCourse | null>(null)
const selectedDay = ref<number>(1)
const loading = ref(false)

// 총 일수 계산
const totalDays = computed(() => {
  if (!course.value?.start_date || !course.value?.end_date) return 3
  const start = new Date(course.value.start_date)
  const end = new Date(course.value.end_date)
  const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  return Math.max(diff, 1)
})

// 선택한 일차의 장소들
const dayItems = computed(() => {
  if (!course.value?.items) return []
  return course.value.items
    .filter(item => item.day_number === selectedDay.value)
    .sort((a, b) => a.sort_order - b.sort_order)
})

// 오늘이 몇일차인지
const todayDay = computed(() => {
  if (!course.value?.start_date) return null
  const start = new Date(course.value.start_date)
  const today = new Date()
  const diff = Math.ceil((today.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  if (diff >= 1 && diff <= totalDays.value) return diff
  return null
})

// 날짜 포맷
function dayDate(dayNum: number): string {
  if (!course.value?.start_date) return ''
  const start = new Date(course.value.start_date)
  start.setDate(start.getDate() + dayNum - 1)
  return `${start.getMonth() + 1}/${start.getDate()}`
}

async function fetchCourse() {
  loading.value = true
  try {
    const { data } = await api.get(`/api/user/travel-courses/${route.params.id}`)
    course.value = data.data
    // 오늘 일차가 있으면 자동 선택
    if (todayDay.value) selectedDay.value = todayDay.value
  } finally {
    loading.value = false
  }
}

async function removeItem(itemId: number) {
  if (!course.value || !confirm('이 장소를 코스에서 제거할까요?')) return
  await api.delete(`/api/user/travel-courses/${course.value.id}/items/${itemId}`)
  fetchCourse()
}

function getPlaceImage(item: any): string | null {
  const p = item.place
  if (!p) return null
  return imageUrl(p.primary_image?.image_url || p.images?.[0]?.image_url)
}

function navigateToPlace(place: any, idx: number) {
  // 이전 장소가 있으면 출발지로 설정
  let srcLat, srcLng, srcName
  if (idx > 0 && dayItems.value[idx - 1]?.place) {
    const prev = dayItems.value[idx - 1].place
    srcLat = Number(prev.latitude)
    srcLng = Number(prev.longitude)
    srcName = prev.name_cn
  }

  startAMapNavigation(
    Number(place.latitude), Number(place.longitude), place.name_cn,
    srcLat, srcLng, srcName
  )
}

function showTaxi(place: any) {
  router.push(`/places/${place.id}?taxi=1`)
}

function viewOnMap() {
  if (!course.value?.items?.length) return
  const items = course.value.items.filter(i => i.day_number === selectedDay.value)
  if (items.length > 0 && items[0].place) {
    router.push(`/map?lat=${items[0].place.latitude}&lng=${items[0].place.longitude}&name=${encodeURIComponent(items[0].place.name_ko)}`)
  }
}

onMounted(fetchCourse)
</script>

<template>
  <div class="pb-20">
    <!-- Header -->
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur px-4 py-3 border-b">
      <div class="flex items-center justify-between">
        <button @click="router.back()" class="text-gray-600">← 뒤로</button>
        <button @click="viewOnMap" class="text-blue-500 text-sm">지도보기</button>
      </div>
      <div v-if="course" class="mt-2">
        <h1 class="text-lg font-bold text-gray-900">{{ course.title }}</h1>
        <p v-if="course.start_date" class="text-xs text-gray-400">
          {{ course.start_date?.substring(0, 10) }}
          <span v-if="course.end_date"> ~ {{ course.end_date?.substring(0, 10) }}</span>
        </p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else-if="course">
      <!-- Day Tabs -->
      <div class="flex gap-2 overflow-x-auto px-4 pt-3 pb-2 scrollbar-hide">
        <button
          v-for="d in totalDays" :key="d"
          @click="selectedDay = d"
          class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors relative"
          :class="selectedDay === d ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
        >
          Day {{ d }}
          <span v-if="dayDate(d)" class="text-[10px] ml-1 opacity-70">{{ dayDate(d) }}</span>
          <!-- 오늘 표시 -->
          <span v-if="todayDay === d" class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
      </div>

      <!-- 오늘 안내 -->
      <div v-if="todayDay === selectedDay" class="mx-4 mt-2 mb-1 px-3 py-1.5 bg-red-50 rounded-lg">
        <p class="text-xs text-red-500 font-medium">오늘 일정입니다</p>
      </div>

      <!-- Place List -->
      <div class="px-4 pt-2 space-y-3">
        <div
          v-for="(item, idx) in dayItems" :key="item.id"
          class="bg-white rounded-xl shadow-sm overflow-hidden"
        >
          <div class="flex gap-3 p-3">
            <!-- 번호 -->
            <div class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-1">
              {{ idx + 1 }}
            </div>

            <!-- 이미지 -->
            <div class="w-16 h-16 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
              <img v-if="getPlaceImage(item)" :src="getPlaceImage(item)!" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-xl text-gray-300">📷</div>
            </div>

            <!-- 정보 -->
            <div class="flex-1 min-w-0" @click="router.push(`/places/${item.place?.id}`)">
              <h3 class="text-sm font-semibold text-gray-800 truncate">{{ item.place?.name_ko }}</h3>
              <p class="text-xs text-gray-400 truncate">{{ item.place?.name_cn }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span
                  v-if="item.place?.category"
                  class="text-[10px] text-white px-1.5 py-0.5 rounded-full"
                  :style="{ backgroundColor: item.place.category.color || '#3b82f6' }"
                >{{ item.place.category.name_ko }}</span>
                <span v-if="item.place?.city" class="text-[10px] text-gray-400">{{ item.place.city.name_ko }}</span>
              </div>
              <p v-if="item.memo" class="text-xs text-gray-500 mt-1">📝 {{ item.memo }}</p>
            </div>

            <!-- 삭제 -->
            <button @click="removeItem(item.id)" class="text-gray-300 text-xs shrink-0 self-start mt-1">✕</button>
          </div>

          <!-- 액션 버튼 -->
          <div class="flex border-t border-gray-50">
            <button
              @click="navigateToPlace(item.place, idx)"
              class="flex-1 py-2 text-xs text-blue-500 font-medium active:bg-gray-50"
            >📍 {{ idx > 0 ? dayItems[idx-1].place?.name_ko + '에서' : '길찾기' }}</button>
            <div class="w-px bg-gray-50"></div>
            <button
              @click="showTaxi(item.place)"
              class="flex-1 py-2 text-xs text-yellow-600 font-medium active:bg-gray-50"
            >🚕 택시</button>
            <div class="w-px bg-gray-50"></div>
            <button
              @click="router.push(`/places/${item.place?.id}`)"
              class="flex-1 py-2 text-xs text-gray-500 font-medium active:bg-gray-50"
            >상세보기</button>
          </div>
        </div>

        <!-- 빈 상태 -->
        <div v-if="dayItems.length === 0" class="text-center py-8">
          <div class="text-3xl mb-2">📋</div>
          <p class="text-gray-400 text-sm">Day {{ selectedDay }}에 추가된 장소가 없습니다</p>
          <button @click="router.push('/places')" class="mt-3 text-blue-500 text-sm">
            + 여행지에서 장소 추가하기
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
