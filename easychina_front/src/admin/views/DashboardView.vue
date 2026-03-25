<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../shared/api'

const stats = ref({ cities: 0, places: 0, tips: 0, phrases: 0 })

onMounted(async () => {
  try {
    const { data } = await api.get('/api/admin/dashboard')
    stats.value = data.data
  } catch {}
})
</script>

<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-6">대시보드</h2>
    <div class="grid grid-cols-4 gap-4">
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <p class="text-sm text-gray-500">등록 도시</p>
        <p class="text-3xl font-bold mt-1">{{ stats.cities }}</p>
      </div>
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <p class="text-sm text-gray-500">등록 장소</p>
        <p class="text-3xl font-bold mt-1">{{ stats.places }}</p>
      </div>
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <p class="text-sm text-gray-500">여행 팁</p>
        <p class="text-3xl font-bold mt-1">{{ stats.tips }}</p>
      </div>
      <div class="bg-white rounded-xl p-6 shadow-sm">
        <p class="text-sm text-gray-500">번역 카드</p>
        <p class="text-3xl font-bold mt-1">{{ stats.phrases }}</p>
      </div>
    </div>
  </div>
</template>
