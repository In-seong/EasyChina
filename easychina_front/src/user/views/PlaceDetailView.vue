<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../shared/api'
import type { Place, ApiResponse } from '../../shared/types/place'
import { imageUrl } from '../../shared/utils/image'
import { useAuth } from '../../shared/composables/useAuth'
import { useBookmark } from '../../shared/composables/useBookmark'

const route = useRoute()
const router = useRouter()
const { isLoggedIn } = useAuth()
const { isBookmarked, toggleBookmark, loadBookmarks } = useBookmark()
const place = ref<Place | null>(null)
const showTaxiModal = ref(false)
const showMiniMap = ref(false)
const showCourseModal = ref(false)
const copied = ref(false)
const bookmarking = ref(false)
const courses = ref<any[]>([])
const selectedCourseId = ref<number | null>(null)
const selectedDayNumber = ref<number>(1)
const addingToCourse = ref(false)
const courseTotalDays = ref(3)

async function handleBookmark() {
  if (!place.value) return
  if (!isLoggedIn.value) {
    router.push('/mypage')
    return
  }
  bookmarking.value = true
  await toggleBookmark(place.value.id)
  bookmarking.value = false
}

async function fetchPlace() {
  const { data } = await api.get<ApiResponse<Place>>(`/api/user/places/${route.params.id}`)
  place.value = data.data
}

function copyAddress() {
  if (!place.value) return
  navigator.clipboard.writeText(place.value.address_cn)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}

async function openCourseModal() {
  if (!isLoggedIn.value) { router.push('/mypage'); return }
  try {
    const { data } = await api.get('/api/user/travel-courses')
    courses.value = data.data
    showCourseModal.value = true
  } catch {}
}

function selectCourse(course: any) {
  selectedCourseId.value = course.id
  selectedDayNumber.value = 1
  if (course.start_date && course.end_date) {
    const start = new Date(course.start_date)
    const end = new Date(course.end_date)
    courseTotalDays.value = Math.max(Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1, 1)
  } else {
    courseTotalDays.value = 3
  }
}

async function addToCourse() {
  if (!selectedCourseId.value || !place.value) return
  addingToCourse.value = true
  try {
    await api.post(`/api/user/travel-courses/${selectedCourseId.value}/items`, {
      place_id: place.value.id,
      day_number: selectedDayNumber.value,
    })
    showCourseModal.value = false
    selectedCourseId.value = null
    alert('코스에 추가되었습니다!')
  } catch {
    alert('추가에 실패했습니다.')
  } finally {
    addingToCourse.value = false
  }
}

function openInMap() {
  if (!place.value) return
  router.push(`/map?lat=${place.value.latitude}&lng=${place.value.longitude}&name=${encodeURIComponent(place.value.name_ko)}`)
}

function startNavigation() {
  if (!place.value) return
  const { latitude, longitude, name_cn } = place.value
  const name = encodeURIComponent(name_cn)

  // iOS: AMap 앱 URL Scheme 시도
  const amapAppUrl = `iosamap://path?sourceApplication=EasyChina&dlat=${latitude}&dlon=${longitude}&dname=${name}&dev=0&t=0`
  // Android: AMap 앱 URL Scheme
  const amapAndroidUrl = `amapuri://route/plan/?dlat=${latitude}&dlon=${longitude}&dname=${name}&dev=0&t=0`
  // 웹 fallback
  const amapWebUrl = `https://uri.amap.com/navigation?to=${longitude},${latitude},${name}&mode=car&src=EasyChina`

  const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent)
  const isAndroid = /Android/i.test(navigator.userAgent)

  if (isIOS) {
    // iOS: 앱 열기 시도 → 실패 시 웹으로
    window.location.href = amapAppUrl
    setTimeout(() => { window.open(amapWebUrl, '_blank') }, 1500)
  } else if (isAndroid) {
    window.location.href = amapAndroidUrl
    setTimeout(() => { window.open(amapWebUrl, '_blank') }, 1500)
  } else {
    // PC: 웹으로
    window.open(amapWebUrl, '_blank')
  }
}

onMounted(() => {
  fetchPlace()
  if (isLoggedIn.value) loadBookmarks()
})
</script>

<template>
  <div v-if="place" class="pb-20">
    <!-- Back Button -->
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur px-4 py-3 flex items-center justify-between border-b">
      <button @click="router.back()" class="text-gray-600">← 뒤로</button>
      <button
        @click="handleBookmark"
        :disabled="bookmarking"
        class="text-xl"
        :class="place && isBookmarked(place.id) ? 'text-yellow-500' : 'text-gray-300'"
      >
        {{ place && isBookmarked(place.id) ? '⭐' : '☆' }}
      </button>
    </div>

    <!-- Images -->
    <div class="aspect-video bg-gray-100 relative overflow-hidden">
      <div v-if="place.images?.length" class="flex overflow-x-auto snap-x snap-mandatory h-full">
        <img
          v-for="img in place.images"
          :key="img.id"
          :src="imageUrl(img.image_url)"
          class="w-full h-full object-cover snap-center shrink-0"
        />
      </div>
      <div v-else class="w-full h-full flex items-center justify-center text-5xl text-gray-300">📷</div>
    </div>

    <!-- Info -->
    <div class="px-4 pt-4">
      <h1 class="text-xl font-bold text-gray-900">{{ place.name_ko }}</h1>
      <p class="text-sm text-gray-500 mt-0.5">
        {{ place.name_cn }}
        <span v-if="place.pinyin" class="text-gray-400">({{ place.pinyin }})</span>
      </p>
      <div class="flex items-center gap-3 mt-2 text-sm text-gray-500">
        <span v-if="place.rating" class="text-yellow-500">⭐ {{ place.rating }}</span>
        <span v-if="place.category"
              class="text-white text-xs px-2 py-0.5 rounded-full"
              :style="{ backgroundColor: place.category.color || '#3b82f6' }">
          {{ place.category.name_ko }}
        </span>
        <span v-if="place.city">{{ place.city.name_ko }}</span>
      </div>
    </div>

    <!-- Details -->
    <div class="px-4 mt-4 space-y-3">
      <div class="flex items-start gap-2 text-sm">
        <span class="shrink-0">📍</span>
        <div>
          <p class="text-gray-700">{{ place.address_ko || place.address_cn }}</p>
          <p class="text-gray-400 text-xs">{{ place.address_cn }}</p>
        </div>
      </div>
      <div v-if="place.business_hours" class="flex items-center gap-2 text-sm">
        <span>🕐</span>
        <span class="text-gray-700">{{ place.business_hours }}</span>
      </div>
      <div v-if="place.price_min || place.price_max" class="flex items-center gap-2 text-sm">
        <span>💰</span>
        <span class="text-gray-700">
          {{ place.price_min ? `${(place.price_min/10000).toFixed(0)}만` : '' }}
          {{ place.price_min && place.price_max ? '~' : '' }}
          {{ place.price_max ? `${(place.price_max/10000).toFixed(0)}만원` : '' }}
        </span>
      </div>
      <div v-if="place.phone" class="flex items-center gap-2 text-sm">
        <span>📞</span>
        <a :href="`tel:${place.phone}`" class="text-blue-500">{{ place.phone }}</a>
      </div>
    </div>

    <!-- Payment -->
    <div class="px-4 mt-4">
      <h3 class="text-sm font-semibold text-gray-800 mb-2">💳 결제 수단</h3>
      <div class="flex gap-3 text-sm">
        <span :class="place.pay_alipay ? 'text-green-500' : 'text-gray-300'">
          {{ place.pay_alipay ? '✅' : '❌' }} 알리페이
        </span>
        <span :class="place.pay_wechat ? 'text-green-500' : 'text-gray-300'">
          {{ place.pay_wechat ? '✅' : '❌' }} 위챗페이
        </span>
        <span :class="place.pay_cash ? 'text-green-500' : 'text-gray-300'">
          {{ place.pay_cash ? '✅' : '❌' }} 현금
        </span>
      </div>
      <div class="flex gap-3 mt-2 text-sm text-gray-600">
        <span>{{ place.has_english_menu ? '✅ 영어메뉴' : '❌ 영어메뉴 없음' }}</span>
        <span v-if="place.restroom_rating">
          🚻 {{ '⭐'.repeat(place.restroom_rating) }}
        </span>
      </div>
    </div>

    <!-- Description -->
    <div v-if="place.description" class="px-4 mt-4">
      <h3 class="text-sm font-semibold text-gray-800 mb-2">📝 설명</h3>
      <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ place.description }}</p>
    </div>

    <!-- Tips -->
    <div v-if="place.tips" class="px-4 mt-4">
      <h3 class="text-sm font-semibold text-gray-800 mb-2">💡 여행 팁</h3>
      <div class="bg-yellow-50 rounded-lg p-3 text-sm text-gray-700 leading-relaxed whitespace-pre-line">
        {{ place.tips }}
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="px-4 mt-6 space-y-2">
      <button
        @click="copyAddress"
        class="w-full py-3 rounded-xl text-sm font-medium bg-gray-100 text-gray-700 active:bg-gray-200"
      >
        {{ copied ? '✅ 복사됨!' : '📋 중국어 주소 복사' }}
      </button>
      <button
        @click="showTaxiModal = true"
        class="w-full py-3 rounded-xl text-sm font-medium bg-yellow-400 text-gray-900 active:bg-yellow-500"
      >
        🚕 택시 기사님께 보여주기
      </button>
      <button
        @click="openCourseModal"
        class="w-full py-3 rounded-xl text-sm font-medium bg-purple-500 text-white active:bg-purple-600"
      >
        📅 여행 코스에 추가
      </button>
      <div class="flex gap-2">
        <button
          @click="openInMap"
          class="flex-1 py-3 rounded-xl text-sm font-medium bg-blue-500 text-white active:bg-blue-600"
        >
          🗺 지도에서 보기
        </button>
        <button
          @click="startNavigation"
          class="flex-1 py-3 rounded-xl text-sm font-medium bg-green-500 text-white active:bg-green-600"
        >
          📍 길찾기
        </button>
      </div>
    </div>

    <!-- Course Add Modal -->
    <Teleport to="body">
      <div v-if="showCourseModal" class="fixed inset-0 z-50 bg-black/30 flex items-end justify-center">
        <div class="bg-white rounded-t-2xl w-full max-w-lg p-5" @click.stop>
          <h3 class="text-lg font-bold mb-3">어떤 코스에 추가할까요?</h3>

          <!-- 코스 목록 -->
          <div v-if="!selectedCourseId" class="space-y-2 max-h-60 overflow-y-auto">
            <button
              v-for="c in courses" :key="c.id"
              @click="selectCourse(c)"
              class="w-full text-left bg-gray-50 rounded-xl p-3 active:bg-gray-100"
            >
              <p class="text-sm font-semibold text-gray-800">📅 {{ c.title }}</p>
              <p v-if="c.start_date" class="text-xs text-gray-400">
                {{ c.start_date?.substring(0,10) }}
                <span v-if="c.end_date"> ~ {{ c.end_date?.substring(0,10) }}</span>
              </p>
            </button>
            <div v-if="courses.length === 0" class="text-center py-4">
              <p class="text-sm text-gray-400">생성된 코스가 없습니다</p>
            </div>
            <button
              @click="showCourseModal = false; router.push('/courses')"
              class="w-full text-center py-3 text-blue-500 text-sm font-medium"
            >
              + 새 코스 만들기
            </button>
          </div>

          <!-- 일차 선택 -->
          <div v-else>
            <p class="text-sm text-gray-600 mb-3">몇 일차에 추가할까요?</p>
            <div class="flex gap-2 flex-wrap mb-4">
              <button
                v-for="d in courseTotalDays" :key="d"
                @click="selectedDayNumber = d"
                class="px-4 py-2 rounded-full text-sm font-medium"
                :class="selectedDayNumber === d ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
              >
                Day {{ d }}
              </button>
            </div>
            <div class="flex gap-2">
              <button @click="selectedCourseId = null" class="flex-1 py-3 rounded-lg text-sm bg-gray-100 text-gray-500">뒤로</button>
              <button
                @click="addToCourse"
                :disabled="addingToCourse"
                class="flex-1 py-3 rounded-lg text-sm bg-blue-500 text-white disabled:opacity-50"
              >
                {{ addingToCourse ? '추가 중...' : '추가하기' }}
              </button>
            </div>
          </div>

          <button @click="showCourseModal = false; selectedCourseId = null" class="w-full mt-3 py-2 text-sm text-gray-400">닫기</button>
        </div>
      </div>
    </Teleport>

    <!-- Taxi Modal -->
    <Teleport to="body">
      <div
        v-if="showTaxiModal"
        class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-center p-8"
        @click="showTaxiModal = false"
      >
        <button class="absolute top-4 right-4 text-gray-400 text-xl">✕</button>

        <!-- 기사님께 보여주는 부분 -->
        <p class="text-lg text-gray-400 mb-2">师傅，请到这里：</p>
        <p class="text-xs text-gray-400 mb-6">(shīfu, qǐng dào zhèlǐ - 기사님, 여기로 가주세요)</p>

        <p class="text-4xl font-bold text-gray-900 text-center mb-1">{{ place.name_cn }}</p>
        <p class="text-sm text-gray-400 text-center mb-4">{{ place.name_ko }}</p>

        <p class="text-2xl text-gray-700 text-center leading-relaxed mb-1">{{ place.address_cn }}</p>
        <p v-if="place.address_ko" class="text-xs text-gray-400 text-center mb-6">{{ place.address_ko }}</p>

        <p class="text-xl text-gray-400 mt-4">谢谢！</p>
        <p class="text-xs text-gray-400 mt-1">(xièxie - 감사합니다)</p>

        <!-- 직접 말해보기 가이드 -->
        <div class="mt-8 w-full max-w-sm bg-gray-50 rounded-xl p-4" @click.stop>
          <p class="text-xs font-semibold text-gray-500 mb-2 text-center">직접 말해보기</p>
          <div class="space-y-2 text-center">
            <div>
              <p class="text-sm text-gray-700">请到 <span class="font-bold">{{ place.name_cn }}</span></p>
              <p class="text-xs text-blue-500">qǐng dào <span class="font-bold">{{ place.pinyin || place.name_cn }}</span></p>
              <p class="text-xs text-gray-400">"{{ place.name_ko }}(으)로 가주세요"</p>
            </div>
            <hr class="border-gray-200" />
            <div>
              <p class="text-sm text-gray-700">请打表</p>
              <p class="text-xs text-blue-500">qǐng dǎ biǎo</p>
              <p class="text-xs text-gray-400">"미터기 켜주세요"</p>
            </div>
            <hr class="border-gray-200" />
            <div>
              <p class="text-sm text-gray-700">到了，谢谢</p>
              <p class="text-xs text-blue-500">dào le, xièxie</p>
              <p class="text-xs text-gray-400">"다 왔어요, 감사합니다"</p>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
