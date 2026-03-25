<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '../../shared/api'
import type { Phrase, PhraseCategory } from '../../shared/types/place'

const phrases = ref<Phrase[]>([])
const categories = ref<PhraseCategory[]>([])
const filterCategory = ref<number | ''>('')

const showForm = ref(false)
const editingId = ref<number | null>(null)
const form = ref({ phrase_category_id: '', text_ko: '', text_cn: '', pinyin: '', sort_order: 0 })

async function fetchData() {
  const { data } = await api.get('/api/admin/phrase-categories')
  categories.value = data.data
  fetchPhrases()
}

async function fetchPhrases() {
  const { data } = await api.get('/api/admin/phrases', {
    params: { phrase_category_id: filterCategory.value || undefined },
  })
  phrases.value = data.data.data || data.data
}

function openCreate() {
  editingId.value = null
  form.value = { phrase_category_id: filterCategory.value ? String(filterCategory.value) : '', text_ko: '', text_cn: '', pinyin: '', sort_order: 0 }
  showForm.value = true
}

function openEdit(p: Phrase) {
  editingId.value = p.id
  form.value = {
    phrase_category_id: String(p.phrase_category_id),
    text_ko: p.text_ko, text_cn: p.text_cn, pinyin: p.pinyin || '', sort_order: 0,
  }
  showForm.value = true
}

async function save() {
  if (editingId.value) {
    await api.put(`/api/admin/phrases/${editingId.value}`, form.value)
  } else {
    await api.post('/api/admin/phrases', form.value)
  }
  showForm.value = false
  fetchPhrases()
}

async function remove(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/admin/phrases/${id}`)
  fetchPhrases()
}

watch(filterCategory, fetchPhrases)
onMounted(fetchData)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">번역카드 관리</h2>
      <button @click="openCreate" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">+ 번역카드 추가</button>
    </div>

    <div class="mb-4">
      <select v-model="filterCategory" class="border rounded-lg px-3 py-2 text-sm">
        <option value="">전체 카테고리</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }} ({{ (c as any).phrases_count || 0 }})</option>
      </select>
    </div>

    <table class="w-full bg-white rounded-xl shadow-sm">
      <thead>
        <tr class="border-b text-left text-sm text-gray-500">
          <th class="p-3">카테고리</th><th class="p-3">한국어</th><th class="p-3">중국어</th>
          <th class="p-3">병음</th><th class="p-3">관리</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in phrases" :key="p.id" class="border-b last:border-0 text-sm">
          <td class="p-3 text-gray-400">{{ p.phrase_category?.name }}</td>
          <td class="p-3 font-medium">{{ p.text_ko }}</td>
          <td class="p-3">{{ p.text_cn }}</td>
          <td class="p-3 text-gray-400 text-xs">{{ p.pinyin }}</td>
          <td class="p-3">
            <button @click="openEdit(p)" class="text-blue-500 text-xs mr-2">수정</button>
            <button @click="remove(p.id)" class="text-red-500 text-xs">삭제</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingId ? '번역카드 수정' : '번역카드 추가' }}</h3>
        <form @submit.prevent="save" class="space-y-3">
          <select v-model="form.phrase_category_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">카테고리 선택 *</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <input v-model="form.text_ko" placeholder="한국어 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.text_cn" placeholder="중국어 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.pinyin" placeholder="병음 (pinyin)" class="w-full border rounded-lg px-3 py-2 text-sm" />
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showForm = false" class="px-4 py-2 text-sm text-gray-500">취소</button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm">저장</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
