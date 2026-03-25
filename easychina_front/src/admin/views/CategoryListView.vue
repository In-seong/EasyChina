<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../shared/api'
import type { Category } from '../../shared/types/place'

const categories = ref<Category[]>([])
const showForm = ref(false)
const editingId = ref<number | null>(null)
const form = ref({ name_ko: '', name_cn: '', icon: '', color: '#3b82f6', sort_order: 0 })

async function fetchCategories() {
  const { data } = await api.get('/api/admin/categories')
  categories.value = data.data
}

function openCreate() {
  editingId.value = null
  form.value = { name_ko: '', name_cn: '', icon: '', color: '#3b82f6', sort_order: 0 }
  showForm.value = true
}

function openEdit(cat: Category) {
  editingId.value = cat.id
  form.value = { name_ko: cat.name_ko, name_cn: cat.name_cn || '', icon: cat.icon || '', color: cat.color || '#3b82f6', sort_order: 0 }
  showForm.value = true
}

async function save() {
  if (editingId.value) {
    await api.put(`/api/admin/categories/${editingId.value}`, form.value)
  } else {
    await api.post('/api/admin/categories', form.value)
  }
  showForm.value = false
  fetchCategories()
}

async function remove(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/admin/categories/${id}`)
  fetchCategories()
}

onMounted(fetchCategories)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">카테고리 관리</h2>
      <button @click="openCreate" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">+ 카테고리 추가</button>
    </div>

    <table class="w-full bg-white rounded-xl shadow-sm">
      <thead>
        <tr class="border-b text-left text-sm text-gray-500">
          <th class="p-3">색상</th><th class="p-3">한국어</th><th class="p-3">중국어</th><th class="p-3">관리</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="cat in categories" :key="cat.id" class="border-b last:border-0 text-sm">
          <td class="p-3"><span class="w-4 h-4 rounded-full inline-block" :style="{ backgroundColor: cat.color || '#ccc' }"></span></td>
          <td class="p-3 font-medium">{{ cat.name_ko }}</td>
          <td class="p-3 text-gray-500">{{ cat.name_cn }}</td>
          <td class="p-3">
            <button @click="openEdit(cat)" class="text-blue-500 text-xs mr-2">수정</button>
            <button @click="remove(cat.id)" class="text-red-500 text-xs">삭제</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showForm" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingId ? '카테고리 수정' : '카테고리 추가' }}</h3>
        <form @submit.prevent="save" class="space-y-3">
          <input v-model="form.name_ko" placeholder="한국어 이름 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.name_cn" placeholder="중국어 이름" class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.icon" placeholder="아이콘 (lucide icon name)" class="w-full border rounded-lg px-3 py-2 text-sm" />
          <div class="flex items-center gap-2">
            <input v-model="form.color" type="color" class="w-10 h-10 rounded border" />
            <span class="text-sm text-gray-500">마커 색상</span>
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
