<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../shared/api'
import type { Place } from '../../shared/types/place'
import PlaceCard from '../components/PlaceCard.vue'

const router = useRouter()
const places = ref<Place[]>([])
const loading = ref(false)

async function fetchBookmarks() {
  const token = localStorage.getItem('token')
  if (!token) return

  loading.value = true
  try {
    const { data } = await api.get('/api/user/bookmarks')
    places.value = data.data.data || data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchBookmarks)
</script>

<template>
  <div class="pb-4">
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur px-4 py-3 border-b">
      <button @click="router.back()" class="text-gray-600">← 북마크</button>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-400 text-sm">불러오는 중...</p>
    </div>

    <div v-else-if="places.length === 0" class="text-center py-12">
      <div class="text-4xl mb-2">🔖</div>
      <p class="text-gray-400 text-sm">북마크한 장소가 없습니다</p>
    </div>

    <div v-else class="px-4 pt-4 grid grid-cols-2 gap-3">
      <PlaceCard v-for="place in places" :key="place.id" :place="place" />
    </div>
  </div>
</template>
