<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const pdfUrl = `${import.meta.env.VITE_API_URL || window.location.origin}/shanghai-metro-map.pdf`
const opened = ref(false)

function openPdf() {
  opened.value = true

  // iOS 앱 → Bridge로 Safari에서 열기
  if (window.webkit?.messageHandlers?.iOSBridge) {
    window.webkit.messageHandlers.iOSBridge.postMessage({ action: 'openExternalUrl', url: pdfUrl })
    return
  }

  // 브라우저 → 새 탭
  const a = document.createElement('a')
  a.href = pdfUrl
  a.target = '_blank'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

onMounted(() => {
  openPdf()
})
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col items-center justify-center px-6 text-center bg-gray-50">
    <div class="text-5xl mb-4">🚇</div>
    <h2 class="text-lg font-bold text-gray-800 mb-2">상하이 지하철 노선도</h2>
    <p class="text-sm text-gray-500 mb-6">PDF 뷰어에서 확대하면 글씨가 선명합니다</p>

    <button
      @click="openPdf"
      class="px-8 py-3 bg-blue-500 text-white rounded-xl text-sm font-medium active:bg-blue-600 shadow-sm mb-4"
    >
      {{ opened ? '다시 열기' : '노선도 열기' }}
    </button>

    <button @click="router.back()" class="text-gray-400 text-sm">← 돌아가기</button>
  </div>
</template>
