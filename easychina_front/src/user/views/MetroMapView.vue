<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const container = ref<HTMLDivElement | null>(null)
const zoomLevel = ref(1)

function zoomIn() {
  zoomLevel.value = Math.min(5, zoomLevel.value + 0.5)
}

function zoomOut() {
  zoomLevel.value = Math.max(1, zoomLevel.value - 0.5)
}

function resetView() {
  zoomLevel.value = 1
  if (container.value) {
    container.value.scrollTop = 0
    container.value.scrollLeft = 0
  }
}

// 핀치 줌
let initialPinchDistance = 0
let initialZoom = 1

function onTouchStart(e: TouchEvent) {
  if (e.touches.length === 2) {
    initialPinchDistance = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    initialZoom = zoomLevel.value
  }
}

function onTouchMove(e: TouchEvent) {
  if (e.touches.length === 2) {
    e.preventDefault()
    const dist = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    zoomLevel.value = Math.max(1, Math.min(5, initialZoom * (dist / initialPinchDistance)))
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
      class="flex-1 overflow-auto overscroll-contain"
      @touchstart="onTouchStart"
      @touchmove="onTouchMove"
    >
      <img
        src="/shanghai-metro-map.jpg"
        alt="상하이 지하철 노선도"
        draggable="false"
        :style="{
          width: (zoomLevel * 100) + 'vw',
          maxWidth: 'none',
          display: 'block',
        }"
      />
    </div>

    <!-- Zoom Controls -->
    <div class="absolute bottom-20 right-3 flex flex-col gap-2 z-50">
      <button @click.stop="zoomIn" class="w-12 h-12 bg-white shadow-lg rounded-full flex items-center justify-center text-xl font-bold active:bg-gray-200">+</button>
      <button @click.stop="zoomOut" class="w-12 h-12 bg-white shadow-lg rounded-full flex items-center justify-center text-xl font-bold active:bg-gray-200">−</button>
      <span class="text-[10px] text-gray-400 text-center">{{ Math.round(zoomLevel * 100) }}%</span>
    </div>
  </div>
</template>
