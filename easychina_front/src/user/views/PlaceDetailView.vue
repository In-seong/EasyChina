<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../shared/api'
import type { Place, ApiResponse } from '../../shared/types/place'
import { navigateTo, showPlaceOnMap, isInApp } from '../../shared/utils/bridge'

const route = useRoute()
const router = useRouter()
const place = ref<Place | null>(null)
const showTaxiModal = ref(false)
const copied = ref(false)

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

function openInMap() {
  if (!place.value) return
  if (isInApp()) {
    showPlaceOnMap(place.value.id, place.value.latitude, place.value.longitude, place.value.name_cn)
  }
}

function startNavigation() {
  if (!place.value) return
  if (isInApp()) {
    navigateTo(place.value.latitude, place.value.longitude, place.value.name_cn)
  }
}

onMounted(fetchPlace)
</script>

<template>
  <div v-if="place" class="pb-20">
    <!-- Back Button -->
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur px-4 py-3 flex items-center justify-between border-b">
      <button @click="router.back()" class="text-gray-600">← 뒤로</button>
      <button class="text-gray-400">🔖</button>
    </div>

    <!-- Images -->
    <div class="aspect-video bg-gray-100 relative overflow-hidden">
      <div v-if="place.images?.length" class="flex overflow-x-auto snap-x snap-mandatory h-full">
        <img
          v-for="img in place.images"
          :key="img.id"
          :src="img.image_url"
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

    <!-- Taxi Modal -->
    <Teleport to="body">
      <div
        v-if="showTaxiModal"
        class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-center p-8"
        @click="showTaxiModal = false"
      >
        <button class="absolute top-4 right-4 text-gray-400 text-xl">✕</button>
        <p class="text-lg text-gray-500 mb-6">师傅，请到这里：</p>
        <p class="text-4xl font-bold text-gray-900 text-center mb-4">{{ place.name_cn }}</p>
        <p class="text-2xl text-gray-700 text-center leading-relaxed">{{ place.address_cn }}</p>
        <p class="text-xl text-gray-500 mt-8">谢谢！</p>
      </div>
    </Teleport>
  </div>
</template>
