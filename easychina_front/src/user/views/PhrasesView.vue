<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../shared/api'
import type { PhraseCategory, Phrase, ApiResponse } from '../../shared/types/place'
import { speakChinese, isInApp } from '../../shared/utils/bridge'

const categories = ref<PhraseCategory[]>([])
const phrases = ref<Phrase[]>([])
const selectedCategory = ref<number | null>(null)
const showBigText = ref(false)
const bigPhrase = ref<Phrase | null>(null)
const copiedId = ref<number | null>(null)

async function fetchCategories() {
  const { data } = await api.get<ApiResponse<PhraseCategory[]>>('/api/user/phrase-categories')
  categories.value = data.data
  if (categories.value.length > 0) {
    selectedCategory.value = categories.value[0].id
    fetchPhrases()
  }
}

async function fetchPhrases() {
  if (!selectedCategory.value) return
  const { data } = await api.get<ApiResponse<Phrase[]>>('/api/user/phrases', {
    params: { phrase_category_id: selectedCategory.value },
  })
  phrases.value = data.data
}

function selectCategory(id: number) {
  selectedCategory.value = id
  fetchPhrases()
}

function speak(text: string) {
  if (isInApp()) {
    speakChinese(text)
  } else {
    const utterance = new SpeechSynthesisUtterance(text)
    utterance.lang = 'zh-CN'
    speechSynthesis.speak(utterance)
  }
}

function copyText(phrase: Phrase) {
  navigator.clipboard.writeText(phrase.text_cn)
  copiedId.value = phrase.id
  setTimeout(() => copiedId.value = null, 2000)
}

function showBig(phrase: Phrase) {
  bigPhrase.value = phrase
  showBigText.value = true
}

onMounted(fetchCategories)
</script>

<template>
  <div class="px-4 pt-4">
    <h1 class="text-xl font-bold mb-3">번역 도우미</h1>

    <!-- Category Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-3 scrollbar-hide">
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="selectCategory(cat.id)"
        class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
        :class="selectedCategory === cat.id ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
      >
        {{ cat.name }}
      </button>
    </div>

    <!-- Phrase Cards -->
    <div class="space-y-3 pb-4">
      <div
        v-for="phrase in phrases"
        :key="phrase.id"
        class="bg-white rounded-xl p-4 shadow-sm"
      >
        <p class="text-sm text-gray-800 font-medium">{{ phrase.text_ko }}</p>
        <p class="text-lg text-gray-900 font-bold mt-1">{{ phrase.text_cn }}</p>
        <p v-if="phrase.pinyin" class="text-xs text-gray-400 mt-0.5">{{ phrase.pinyin }}</p>
        <div class="flex gap-2 mt-3">
          <button
            @click="speak(phrase.text_cn)"
            class="flex-1 py-2 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 active:bg-blue-100"
          >
            🔊 재생
          </button>
          <button
            @click="copyText(phrase)"
            class="flex-1 py-2 rounded-lg text-xs font-medium bg-gray-50 text-gray-600 active:bg-gray-100"
          >
            {{ copiedId === phrase.id ? '✅ 복사됨' : '📋 복사' }}
          </button>
          <button
            @click="showBig(phrase)"
            class="flex-1 py-2 rounded-lg text-xs font-medium bg-yellow-50 text-yellow-700 active:bg-yellow-100"
          >
            📱 크게
          </button>
        </div>
      </div>
    </div>

    <div v-if="phrases.length === 0" class="text-center py-12">
      <div class="text-4xl mb-2">💬</div>
      <p class="text-gray-400 text-sm">카테고리를 선택해주세요</p>
    </div>
  </div>

  <!-- Big Text Modal -->
  <Teleport to="body">
    <div
      v-if="showBigText && bigPhrase"
      class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-center p-8"
      @click="showBigText = false"
    >
      <button class="absolute top-4 right-4 text-gray-400 text-xl">✕</button>
      <p class="text-4xl font-bold text-gray-900 text-center mb-6">{{ bigPhrase.text_cn }}</p>
      <p class="text-xl text-gray-500 text-center">{{ bigPhrase.text_ko }}</p>
      <p v-if="bigPhrase.pinyin" class="text-sm text-gray-400 mt-2">{{ bigPhrase.pinyin }}</p>
    </div>
  </Teleport>
</template>
