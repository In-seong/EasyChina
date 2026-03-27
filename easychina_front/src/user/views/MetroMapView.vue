<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const pdfUrl = `${window.location.origin}/shanghai-metro-map.pdf`

onMounted(() => {
  // 바로 PDF를 새 탭으로 열고 뒤로 가기
  window.open(pdfUrl, '_blank')
  router.back()
})
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col items-center justify-center px-6 text-center">
    <div class="text-5xl mb-4">🚇</div>
    <p class="text-gray-500 text-sm mb-4">노선도를 여는 중...</p>
    <p class="text-gray-400 text-xs mb-6">자동으로 열리지 않으면 아래 버튼을 눌러주세요</p>
    <a
      :href="pdfUrl"
      target="_blank"
      class="px-6 py-3 bg-blue-500 text-white rounded-xl text-sm font-medium active:bg-blue-600"
    >
      노선도 열기
    </a>
    <button @click="router.back()" class="mt-4 text-gray-400 text-sm">← 돌아가기</button>
  </div>
</template>
