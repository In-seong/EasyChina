<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../shared/api'
import type { Banner, City } from '../../shared/types/place'

const banners = ref<Banner[]>([])
const cities = ref<City[]>([])

const showForm = ref(false)
const editingId = ref<number | null>(null)
const form = ref({
  city_id: '', content: '', type: 'INFO',
  start_date: '', end_date: '', is_active: true, sort_order: 0,
})

async function fetchData() {
  const [bannerRes, cityRes] = await Promise.all([
    api.get('/api/admin/banners'),
    api.get('/api/admin/cities'),
  ])
  banners.value = bannerRes.data.data
  cities.value = cityRes.data.data
}

function openCreate() {
  editingId.value = null
  form.value = { city_id: '', content: '', type: 'INFO', start_date: '', end_date: '', is_active: true, sort_order: 0 }
  showForm.value = true
}

function openEdit(b: Banner) {
  editingId.value = b.id
  form.value = {
    city_id: b.city_id ? String(b.city_id) : '',
    content: b.content, type: b.type,
    start_date: '', end_date: '',
    is_active: true, sort_order: 0,
  }
  showForm.value = true
}

async function save() {
  const payload = {
    ...form.value,
    city_id: form.value.city_id || null,
    start_date: form.value.start_date || null,
    end_date: form.value.end_date || null,
  }
  if (editingId.value) {
    await api.put(`/api/admin/banners/${editingId.value}`, payload)
  } else {
    await api.post('/api/admin/banners', payload)
  }
  showForm.value = false
  fetchData()
}

async function remove(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/admin/banners/${id}`)
  fetchData()
}

function typeLabel(t: string) {
  return { INFO: '정보', WARNING: '주의', URGENT: '긴급' }[t] || t
}

function typeColor(t: string) {
  return { INFO: 'bg-blue-100 text-blue-700', WARNING: 'bg-yellow-100 text-yellow-700', URGENT: 'bg-red-100 text-red-700' }[t] || ''
}

onMounted(fetchData)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">배너 관리</h2>
      <button @click="openCreate" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">+ 배너 추가</button>
    </div>

    <table class="w-full bg-white rounded-xl shadow-sm">
      <thead>
        <tr class="border-b text-left text-sm text-gray-500">
          <th class="p-3">유형</th><th class="p-3">도시</th><th class="p-3">내용</th><th class="p-3">관리</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in banners" :key="b.id" class="border-b last:border-0 text-sm">
          <td class="p-3">
            <span class="text-xs px-2 py-0.5 rounded-full" :class="typeColor(b.type)">{{ typeLabel(b.type) }}</span>
          </td>
          <td class="p-3 text-gray-500">{{ b.city?.name_ko || '전체' }}</td>
          <td class="p-3 font-medium">{{ b.content }}</td>
          <td class="p-3">
            <button @click="openEdit(b)" class="text-blue-500 text-xs mr-2">수정</button>
            <button @click="remove(b.id)" class="text-red-500 text-xs">삭제</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingId ? '배너 수정' : '배너 추가' }}</h3>
        <form @submit.prevent="save" class="space-y-3">
          <select v-model="form.type" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="INFO">정보</option>
            <option value="WARNING">주의</option>
            <option value="URGENT">긴급</option>
          </select>
          <select v-model="form.city_id" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">전체 도시</option>
            <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name_ko }}</option>
          </select>
          <input v-model="form.content" placeholder="배너 내용 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs text-gray-500">시작일</label>
              <input v-model="form.start_date" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="text-xs text-gray-500">종료일 (비우면 상시)</label>
              <input v-model="form.end_date" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" v-model="form.is_active" /> 활성
          </label>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showForm = false" class="px-4 py-2 text-sm text-gray-500">취소</button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm">저장</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
