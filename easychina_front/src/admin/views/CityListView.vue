<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../shared/api'
import type { City } from '../../shared/types/place'

const cities = ref<City[]>([])
const showForm = ref(false)
const editingId = ref<number | null>(null)
const form = ref({ name_ko: '', name_cn: '', name_en: '', pinyin: '', latitude: '', longitude: '', sort_order: 0 })

async function fetchCities() {
  const { data } = await api.get('/api/admin/cities')
  cities.value = data.data
}

function openCreate() {
  editingId.value = null
  form.value = { name_ko: '', name_cn: '', name_en: '', pinyin: '', latitude: '', longitude: '', sort_order: 0 }
  showForm.value = true
}

function openEdit(city: City) {
  editingId.value = city.id
  form.value = {
    name_ko: city.name_ko, name_cn: city.name_cn, name_en: city.name_en || '',
    pinyin: city.pinyin || '', latitude: String(city.latitude), longitude: String(city.longitude),
    sort_order: 0,
  }
  showForm.value = true
}

async function save() {
  if (editingId.value) {
    await api.put(`/api/admin/cities/${editingId.value}`, form.value)
  } else {
    await api.post('/api/admin/cities', form.value)
  }
  showForm.value = false
  fetchCities()
}

async function remove(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/admin/cities/${id}`)
  fetchCities()
}

onMounted(fetchCities)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">도시 관리</h2>
      <button @click="openCreate" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">+ 도시 추가</button>
    </div>

    <table class="w-full bg-white rounded-xl shadow-sm">
      <thead>
        <tr class="border-b text-left text-sm text-gray-500">
          <th class="p-3">한국어</th><th class="p-3">중국어</th><th class="p-3">영어</th>
          <th class="p-3">위도/경도</th><th class="p-3">관리</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="city in cities" :key="city.id" class="border-b last:border-0 text-sm">
          <td class="p-3 font-medium">{{ city.name_ko }}</td>
          <td class="p-3">{{ city.name_cn }}</td>
          <td class="p-3 text-gray-500">{{ city.name_en }}</td>
          <td class="p-3 text-gray-400 text-xs">{{ city.latitude }}, {{ city.longitude }}</td>
          <td class="p-3">
            <button @click="openEdit(city)" class="text-blue-500 text-xs mr-2">수정</button>
            <button @click="remove(city.id)" class="text-red-500 text-xs">삭제</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingId ? '도시 수정' : '도시 추가' }}</h3>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name_ko" placeholder="한국어 이름 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.name_cn" placeholder="중국어 이름 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.name_en" placeholder="영어 이름" class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.pinyin" placeholder="병음" class="w-full border rounded-lg px-3 py-2 text-sm" />
          <div class="flex gap-2">
            <input v-model="form.latitude" placeholder="위도 *" required class="flex-1 border rounded-lg px-3 py-2 text-sm" />
            <input v-model="form.longitude" placeholder="경도 *" required class="flex-1 border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showForm = false" class="px-4 py-2 text-sm text-gray-500">취소</button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm">저장</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
