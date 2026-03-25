<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../shared/api'
import type { Place, City, Category } from '../../shared/types/place'

const router = useRouter()
const places = ref<Place[]>([])
const cities = ref<City[]>([])
const categories = ref<Category[]>([])
const filterCity = ref<number | ''>('')
const filterCategory = ref<number | ''>('')
const filterStatus = ref('')

async function fetchData() {
  const [p, c, cat] = await Promise.all([
    api.get('/api/admin/places', { params: { city_id: filterCity.value || undefined, category_id: filterCategory.value || undefined, status: filterStatus.value || undefined } }),
    api.get('/api/admin/cities'),
    api.get('/api/admin/categories'),
  ])
  places.value = p.data.data.data || p.data.data
  cities.value = c.data.data
  categories.value = cat.data.data
}

async function remove(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/admin/places/${id}`)
  fetchData()
}

function statusLabel(s: string) {
  return { PUBLIC: '공개', PRIVATE: '비공개', DRAFT: '임시' }[s] || s
}

function statusColor(s: string) {
  return { PUBLIC: 'bg-green-100 text-green-700', PRIVATE: 'bg-gray-100 text-gray-600', DRAFT: 'bg-yellow-100 text-yellow-700' }[s] || ''
}

onMounted(fetchData)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">장소 관리</h2>
      <button @click="router.push('/places/create')" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">+ 장소 추가</button>
    </div>

    <div class="flex gap-3 mb-4">
      <select v-model="filterCity" @change="fetchData" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">전체 도시</option>
        <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name_ko }}</option>
      </select>
      <select v-model="filterCategory" @change="fetchData" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">전체 카테고리</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_ko }}</option>
      </select>
      <select v-model="filterStatus" @change="fetchData" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">전체 상태</option>
        <option value="PUBLIC">공개</option>
        <option value="PRIVATE">비공개</option>
        <option value="DRAFT">임시</option>
      </select>
    </div>

    <table class="w-full bg-white rounded-xl shadow-sm">
      <thead>
        <tr class="border-b text-left text-sm text-gray-500">
          <th class="p-3">이름</th><th class="p-3">도시</th><th class="p-3">카테고리</th>
          <th class="p-3">상태</th><th class="p-3">조회/북마크</th><th class="p-3">관리</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="place in places" :key="place.id" class="border-b last:border-0 text-sm">
          <td class="p-3">
            <p class="font-medium">{{ place.name_ko }}</p>
            <p class="text-xs text-gray-400">{{ place.name_cn }}</p>
          </td>
          <td class="p-3 text-gray-500">{{ place.city?.name_ko }}</td>
          <td class="p-3">
            <span class="text-xs px-2 py-0.5 rounded-full text-white" :style="{ backgroundColor: place.category?.color || '#999' }">
              {{ place.category?.name_ko }}
            </span>
          </td>
          <td class="p-3">
            <span class="text-xs px-2 py-0.5 rounded-full" :class="statusColor(place.status)">{{ statusLabel(place.status) }}</span>
          </td>
          <td class="p-3 text-xs text-gray-400">{{ place.view_count }} / {{ place.bookmark_count }}</td>
          <td class="p-3">
            <button @click="router.push(`/places/${place.id}/edit`)" class="text-blue-500 text-xs mr-2">수정</button>
            <button @click="remove(place.id)" class="text-red-500 text-xs">삭제</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
