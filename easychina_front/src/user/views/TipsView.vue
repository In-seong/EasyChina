<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../shared/api'
import type { TipCategory, Banner, ApiResponse } from '../../shared/types/place'
import { isOffline, getCachedTipCategories, getCachedBanners } from '../../shared/utils/offline'

const router = useRouter()
const tipCategories = ref<TipCategory[]>([])
const banners = ref<Banner[]>([])

async function fetchData() {
  try {
    const [catRes, bannerRes] = await Promise.all([
      api.get<ApiResponse<TipCategory[]>>('/api/user/tip-categories'),
      api.get<ApiResponse<Banner[]>>('/api/user/banners'),
    ])
    tipCategories.value = catRes.data.data
    banners.value = bannerRes.data.data
  } catch {
    // 오프라인 폴백
    tipCategories.value = await getCachedTipCategories()
    banners.value = await getCachedBanners()
  }
}

function bannerTypeStyle(type: string) {
  switch (type) {
    case 'URGENT': return 'bg-red-50 border-red-200 text-red-700'
    case 'WARNING': return 'bg-yellow-50 border-yellow-200 text-yellow-700'
    default: return 'bg-blue-50 border-blue-200 text-blue-700'
  }
}

function bannerIcon(type: string) {
  switch (type) {
    case 'URGENT': return '🚨'
    case 'WARNING': return '⚠️'
    default: return '💡'
  }
}

onMounted(fetchData)
</script>

<template>
  <div class="px-4 pt-4 pb-4">
    <h1 class="text-xl font-bold mb-3">여행수첩</h1>

    <!-- Live Banners -->
    <div v-if="banners.length" class="space-y-2 mb-4">
      <div
        v-for="banner in banners"
        :key="banner.id"
        class="rounded-lg border p-3 text-sm"
        :class="bannerTypeStyle(banner.type)"
      >
        <span class="mr-1">{{ bannerIcon(banner.type) }}</span>
        <span v-if="banner.city" class="font-medium">[{{ banner.city.name_ko }}]</span>
        {{ banner.content }}
      </div>
    </div>

    <!-- Tip Categories -->
    <div class="space-y-2">
      <button
        v-for="cat in tipCategories"
        :key="cat.id"
        @click="router.push(`/tips/${cat.id}`)"
        class="w-full bg-white rounded-xl p-4 flex items-center justify-between shadow-sm active:bg-gray-50"
      >
        <div class="flex items-center gap-3">
          <span class="text-xl">{{ cat.icon || '📋' }}</span>
          <span class="text-sm font-medium text-gray-800">{{ cat.name }}</span>
        </div>
        <span class="text-gray-400 text-sm">›</span>
      </button>
    </div>
  </div>
</template>
