<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '../../shared/api'
import type { Tip, TipCategory, City } from '../../shared/types/place'

const tips = ref<Tip[]>([])
const tipCategories = ref<TipCategory[]>([])
const cities = ref<City[]>([])
const filterCategory = ref<number | ''>('')

const showForm = ref(false)
const editingId = ref<number | null>(null)
const form = ref({
  tip_category_id: '', city_id: '',
  title: '', content: '', image_url: '', sort_order: 0, status: 'PUBLIC',
})

async function fetchData() {
  const [catRes, cityRes] = await Promise.all([
    api.get('/api/admin/tip-categories'),
    api.get('/api/admin/cities'),
  ])
  tipCategories.value = catRes.data.data
  cities.value = cityRes.data.data
  fetchTips()
}

async function fetchTips() {
  const { data } = await api.get('/api/admin/tips', {
    params: { tip_category_id: filterCategory.value || undefined },
  })
  tips.value = data.data.data || data.data
}

function openCreate() {
  editingId.value = null
  form.value = { tip_category_id: '', city_id: '', title: '', content: '', image_url: '', sort_order: 0, status: 'PUBLIC' }
  showForm.value = true
}

function openEdit(tip: Tip) {
  editingId.value = tip.id
  form.value = {
    tip_category_id: String(tip.tip_category_id), city_id: tip.city_id ? String(tip.city_id) : '',
    title: tip.title, content: tip.content, image_url: tip.image_url || '',
    sort_order: 0, status: 'PUBLIC',
  }
  showForm.value = true
}

async function save() {
  const payload = {
    ...form.value,
    city_id: form.value.city_id || null,
  }
  if (editingId.value) {
    await api.put(`/api/admin/tips/${editingId.value}`, payload)
  } else {
    await api.post('/api/admin/tips', payload)
  }
  showForm.value = false
  fetchTips()
}

async function remove(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/admin/tips/${id}`)
  fetchTips()
}

async function uploadImage(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  const formData = new FormData()
  formData.append('image', file)
  const { data } = await api.post('/api/admin/upload/tip-image', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  form.value.image_url = data.data.image_url
}

watch(filterCategory, fetchTips)
onMounted(fetchData)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">여행팁 관리</h2>
      <button @click="openCreate" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">+ 팁 추가</button>
    </div>

    <div class="mb-4">
      <select v-model="filterCategory" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">전체 카테고리</option>
        <option v-for="c in tipCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <table class="w-full bg-white rounded-xl shadow-sm">
      <thead>
        <tr class="border-b text-left text-sm text-gray-500">
          <th class="p-3">카테고리</th><th class="p-3">제목</th><th class="p-3">도시</th>
          <th class="p-3">이미지</th><th class="p-3">관리</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="tip in tips" :key="tip.id" class="border-b last:border-0 text-sm">
          <td class="p-3 text-gray-500">{{ tip.tip_category?.name }}</td>
          <td class="p-3 font-medium">{{ tip.title }}</td>
          <td class="p-3 text-gray-400">{{ tip.city?.name_ko || '전체' }}</td>
          <td class="p-3">
            <span v-if="tip.image_url" class="text-green-500 text-xs">있음</span>
            <span v-else class="text-gray-300 text-xs">없음</span>
          </td>
          <td class="p-3">
            <button @click="openEdit(tip)" class="text-blue-500 text-xs mr-2">수정</button>
            <button @click="remove(tip.id)" class="text-red-500 text-xs">삭제</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg max-h-[80vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">{{ editingId ? '팁 수정' : '팁 추가' }}</h3>
        <form @submit.prevent="save" class="space-y-3">
          <select v-model="form.tip_category_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">카테고리 선택 *</option>
            <option v-for="c in tipCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <select v-model="form.city_id" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">전체 공통</option>
            <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name_ko }}</option>
          </select>
          <input v-model="form.title" placeholder="제목 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <textarea v-model="form.content" placeholder="내용 (HTML 가능) *" required rows="6" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
          <div>
            <label class="text-xs text-gray-500">이미지</label>
            <input type="file" accept="image/*" @change="uploadImage" class="w-full text-sm" />
            <img v-if="form.image_url" :src="form.image_url" class="mt-2 h-20 rounded" />
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
