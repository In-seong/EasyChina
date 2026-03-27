<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const container = ref<HTMLDivElement | null>(null)

// 이미지 너비를 %로 조절 (100% = 화면 맞춤, 300% = 3배 확대)
const imgWidth = ref(100)

let initialPinchDistance = 0
let initialWidth = 100

function onTouchStart(e: TouchEvent) {
  if (e.touches.length === 2) {
    initialPinchDistance = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    initialWidth = imgWidth.value
  }
}

function onTouchMove(e: TouchEvent) {
  if (e.touches.length === 2) {
    e.preventDefault()
    const dist = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    imgWidth.value = Math.max(100, Math.min(800, initialWidth * (dist / initialPinchDistance)))
  }
}

function zoomIn() { imgWidth.value = Math.min(800, imgWidth.value + 50) }
function zoomOut() { imgWidth.value = Math.max(100, imgWidth.value - 50) }
function resetView() {
  imgWidth.value = 100
  if (container.value) {
    container.value.scrollTop = 0
    container.value.scrollLeft = 0
  }
}
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col bg-gray-50 relative">
    <!-- Header -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-b shrink-0 z-10">
      <button @click="router.back()" class="text-gray-600">← 뒤로</button>
      <h1 class="text-sm font-bold">상하이 지하철 노선도</h1>
      <button @click="resetView" class="text-xs text-blue-500">초기화</button>
    </div>

    <!-- Scrollable Map -->
    <div
      ref="container"
      class="flex-1 overflow-auto"
      @touchstart="onTouchStart"
      @touchmove="onTouchMove"
    >
      <img
        src="/shanghai-metro-map.jpg"
        alt="상하이 지하철 노선도"
        :style="{ width: imgWidth + '%' }"
        draggable="false"
      />
    </div>

    <!-- Zoom -->
    <div class="absolute bottom-20 right-3 flex flex-col gap-2 z-10">
      <button @click="zoomIn" class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg font-bold active:bg-gray-100">+</button>
      <button @click="zoomOut" class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg font-bold active:bg-gray-100">−</button>
    </div>
  </div>
</template>
