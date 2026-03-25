<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const menuItems = [
  { icon: '🔖', label: '북마크한 장소', route: '/bookmarks' },
  { icon: '🗺', label: '나의 여행 코스', route: '/courses' },
]
</script>

<template>
  <div class="px-4 pt-4 pb-4">
    <h1 class="text-xl font-bold mb-4">마이페이지</h1>

    <div class="space-y-2">
      <button
        v-for="item in menuItems"
        :key="item.route"
        @click="router.push(item.route)"
        class="w-full bg-white rounded-xl p-4 flex items-center justify-between shadow-sm active:bg-gray-50"
      >
        <div class="flex items-center gap-3">
          <span class="text-xl">{{ item.icon }}</span>
          <span class="text-sm font-medium text-gray-800">{{ item.label }}</span>
        </div>
        <span class="text-gray-400 text-sm">›</span>
      </button>
    </div>

    <div class="mt-6 space-y-2">
      <div class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-xs text-gray-400">앱 버전 1.0.0</p>
      </div>
    </div>
  </div>
</template>
