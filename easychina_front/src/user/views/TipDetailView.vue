<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../shared/api'
import type { Tip, TipCategory, ApiResponse } from '../../shared/types/place'

const route = useRoute()
const router = useRouter()
const tips = ref<Tip[]>([])
const category = ref<TipCategory | null>(null)

async function fetchTips() {
  const { data } = await api.get<ApiResponse<{ category: TipCategory; tips: Tip[] }>>(
    `/api/user/tip-categories/${route.params.id}`
  )
  category.value = data.data.category
  tips.value = data.data.tips
}

onMounted(fetchTips)
</script>

<template>
  <div class="pb-4">
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur px-4 py-3 border-b">
      <button @click="router.back()" class="text-gray-600">
        ← {{ category?.name || '여행팁' }}
      </button>
    </div>

    <div class="px-4 pt-4 space-y-4">
      <div
        v-for="tip in tips"
        :key="tip.id"
        class="bg-white rounded-xl p-4 shadow-sm"
      >
        <h3 class="text-sm font-bold text-gray-800 mb-2">{{ tip.title }}</h3>
        <img
          v-if="tip.image_url"
          :src="tip.image_url"
          class="w-full rounded-lg mb-3"
        />
        <div class="text-sm text-gray-600 leading-relaxed" v-html="tip.content"></div>
        <p v-if="tip.city" class="text-xs text-blue-500 mt-2">
          📍 {{ tip.city.name_ko }} 해당
        </p>
      </div>

      <div v-if="tips.length === 0" class="text-center py-12">
        <p class="text-gray-400 text-sm">등록된 팁이 없습니다</p>
      </div>
    </div>
  </div>
</template>
