<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import api from '../../shared/api'
import type { PhraseCategory, Phrase, ApiResponse } from '../../shared/types/place'
import { speakChinese, isInApp } from '../../shared/utils/bridge'

const FAVORITES_KEY = 'easychina_phrase_favorites'

const categories = ref<PhraseCategory[]>([])
const allPhrases = ref<Phrase[]>([])
const phrasesByCategory = ref<Map<number, Phrase[]>>(new Map())
const selectedCategory = ref<number | string | null>(null)
const showBigText = ref(false)
const bigPhrase = ref<Phrase | null>(null)
const copiedId = ref<number | null>(null)
const searchQuery = ref('')
const favoriteIds = ref<Set<number>>(new Set())
const loadedCategories = ref<Set<number>>(new Set())

function loadFavorites() {
  try {
    const stored = localStorage.getItem(FAVORITES_KEY)
    if (stored) {
      favoriteIds.value = new Set(JSON.parse(stored))
    }
  } catch {
    favoriteIds.value = new Set()
  }
}

function saveFavorites() {
  localStorage.setItem(FAVORITES_KEY, JSON.stringify([...favoriteIds.value]))
}

function toggleFavorite(phraseId: number) {
  if (favoriteIds.value.has(phraseId)) {
    favoriteIds.value.delete(phraseId)
  } else {
    favoriteIds.value.add(phraseId)
  }
  favoriteIds.value = new Set(favoriteIds.value)
  saveFavorites()
}

function isFavorite(phraseId: number): boolean {
  return favoriteIds.value.has(phraseId)
}

async function fetchCategories() {
  try {
    const { data } = await api.get<ApiResponse<PhraseCategory[]>>('/api/user/phrase-categories')
    categories.value = data.data
  } catch {
    // 오프라인 폴백
    const { getCachedPhraseCategories } = await import('../../shared/utils/offline')
    categories.value = await getCachedPhraseCategories()
  }
  if (categories.value.length > 0) {
    selectedCategory.value = 'favorites'
    await fetchAllPhrases()
  }
}

async function fetchAllPhrases() {
  try {
    const promises = categories.value.map(async (cat) => {
      if (!loadedCategories.value.has(cat.id)) {
        const { data } = await api.get<ApiResponse<Phrase[]>>('/api/user/phrases', {
          params: { phrase_category_id: cat.id },
        })
        phrasesByCategory.value.set(cat.id, data.data)
        loadedCategories.value.add(cat.id)
      }
    })
    await Promise.all(promises)
  } catch {
    // 오프라인 폴백
    const { getCachedPhrases } = await import('../../shared/utils/offline')
    const cached = await getCachedPhrases()
    for (const cat of categories.value) {
      phrasesByCategory.value.set(cat.id, cached.filter(p => p.phrase_category_id === cat.id))
      loadedCategories.value.add(cat.id)
    }
  }
  // Flatten all phrases
  const all: Phrase[] = []
  phrasesByCategory.value.forEach((phrases) => {
    all.push(...phrases)
  })
  allPhrases.value = all
}

const isSearching = computed(() => searchQuery.value.trim().length > 0)

const displayPhrases = computed(() => {
  // Search mode: filter across all categories
  if (isSearching.value) {
    const query = searchQuery.value.trim().toLowerCase()
    return allPhrases.value.filter((p) =>
      p.text_ko.toLowerCase().includes(query) ||
      p.text_cn.toLowerCase().includes(query) ||
      (p.pinyin && p.pinyin.toLowerCase().includes(query))
    )
  }

  // Favorites tab
  if (selectedCategory.value === 'favorites') {
    return allPhrases.value.filter((p) => favoriteIds.value.has(p.id))
  }

  // Category tab
  if (selectedCategory.value && typeof selectedCategory.value === 'number') {
    return phrasesByCategory.value.get(selectedCategory.value) || []
  }

  return []
})

function selectCategory(id: number | string) {
  selectedCategory.value = id
  searchQuery.value = ''
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
  setTimeout(() => (copiedId.value = null), 2000)
}

function showBig(phrase: Phrase) {
  bigPhrase.value = phrase
  showBigText.value = true
}

function clearSearch() {
  searchQuery.value = ''
}

onMounted(() => {
  loadFavorites()
  fetchCategories()
})
</script>

<template>
  <div class="px-4 pt-4">
    <h1 class="text-xl font-bold mb-3">번역 도우미</h1>

    <!-- Search Bar -->
    <div class="relative mb-3">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="문구 검색 (한국어, 중국어, 병음)"
        class="w-full pl-10 pr-10 py-2.5 bg-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:bg-white transition-colors"
      />
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 active:text-gray-600"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Category Tabs (hidden during search) -->
    <div v-if="!isSearching" class="flex gap-2 overflow-x-auto pb-3 scrollbar-hide">
      <button
        @click="selectCategory('favorites')"
        class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
        :class="selectedCategory === 'favorites' ? 'bg-yellow-400 text-white' : 'bg-yellow-50 text-yellow-600'"
      >
        <span class="mr-0.5">{{ selectedCategory === 'favorites' ? '\u2B50' : '\u2606' }}</span> 즐겨찾기
      </button>
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

    <!-- Search mode indicator -->
    <div v-if="isSearching" class="pb-3">
      <p class="text-xs text-gray-400">
        전체 검색 결과 <span class="font-semibold text-gray-600">{{ displayPhrases.length }}</span>건
      </p>
    </div>

    <!-- Phrase Cards -->
    <div class="space-y-3 pb-4">
      <div
        v-for="phrase in displayPhrases"
        :key="phrase.id"
        class="bg-white rounded-xl p-4 shadow-sm relative"
      >
        <!-- Favorite Button -->
        <button
          @click.stop="toggleFavorite(phrase.id)"
          class="absolute top-3 right-3 text-xl leading-none active:scale-125 transition-transform"
          :aria-label="isFavorite(phrase.id) ? '즐겨찾기 해제' : '즐겨찾기 추가'"
        >
          <span v-if="isFavorite(phrase.id)" class="text-yellow-400">&#x2B50;</span>
          <span v-else class="text-gray-300">&#x2606;</span>
        </button>

        <p class="text-sm text-gray-800 font-medium pr-8">{{ phrase.text_ko }}</p>
        <p class="text-lg text-gray-900 font-bold mt-1">{{ phrase.text_cn }}</p>
        <p v-if="phrase.pinyin" class="text-xs text-gray-400 mt-0.5">{{ phrase.pinyin }}</p>
        <div class="flex gap-2 mt-3">
          <button
            @click="speak(phrase.text_cn)"
            class="flex-1 py-2 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 active:bg-blue-100"
          >
            <span class="mr-0.5">&#x1F50A;</span> 재생
          </button>
          <button
            @click="copyText(phrase)"
            class="flex-1 py-2 rounded-lg text-xs font-medium bg-gray-50 text-gray-600 active:bg-gray-100"
          >
            {{ copiedId === phrase.id ? '\u2705 복사됨' : '\uD83D\uDCCB 복사' }}
          </button>
          <button
            @click="showBig(phrase)"
            class="flex-1 py-2 rounded-lg text-xs font-medium bg-yellow-50 text-yellow-700 active:bg-yellow-100"
          >
            <span class="mr-0.5">&#x1F4F1;</span> 크게
          </button>
        </div>
      </div>
    </div>

    <!-- Empty States -->
    <div v-if="displayPhrases.length === 0 && isSearching" class="text-center py-12">
      <div class="text-4xl mb-2">&#x1F50D;</div>
      <p class="text-gray-400 text-sm">검색 결과가 없습니다</p>
    </div>

    <div v-if="displayPhrases.length === 0 && !isSearching && selectedCategory === 'favorites'" class="text-center py-12">
      <div class="text-4xl mb-2">&#x2B50;</div>
      <p class="text-gray-400 text-sm">즐겨찾기한 문구가 없습니다</p>
      <p class="text-gray-300 text-xs mt-1">문구 카드의 별 아이콘을 눌러 추가하세요</p>
    </div>

    <div v-if="displayPhrases.length === 0 && !isSearching && selectedCategory !== 'favorites'" class="text-center py-12">
      <div class="text-4xl mb-2">&#x1F4AC;</div>
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
      <button class="absolute top-4 right-4 text-gray-400 text-xl" aria-label="닫기">&#x2715;</button>
      <p class="text-4xl font-bold text-gray-900 text-center mb-6">{{ bigPhrase.text_cn }}</p>
      <p class="text-xl text-gray-500 text-center">{{ bigPhrase.text_ko }}</p>
      <p v-if="bigPhrase.pinyin" class="text-sm text-gray-400 mt-2">{{ bigPhrase.pinyin }}</p>
    </div>
  </Teleport>
</template>
