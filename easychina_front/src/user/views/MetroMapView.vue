<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const scale = ref(1)
const translateX = ref(0)
const translateY = ref(0)
const isDragging = ref(false)

let startX = 0
let startY = 0
let initialPinchDistance = 0
let initialScale = 1

function onTouchStart(e: TouchEvent) {
  if (e.touches.length === 2) {
    initialPinchDistance = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    initialScale = scale.value
  } else if (e.touches.length === 1) {
    isDragging.value = true
    startX = e.touches[0].clientX - translateX.value
    startY = e.touches[0].clientY - translateY.value
  }
}

function onTouchMove(e: TouchEvent) {
  e.preventDefault()
  if (e.touches.length === 2) {
    const dist = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    scale.value = Math.max(1, Math.min(6, initialScale * (dist / initialPinchDistance)))
  } else if (e.touches.length === 1 && isDragging.value) {
    translateX.value = e.touches[0].clientX - startX
    translateY.value = e.touches[0].clientY - startY
  }
}

function onTouchEnd() { isDragging.value = false }

function zoomIn() { scale.value = Math.min(6, scale.value + 0.5) }
function zoomOut() { scale.value = Math.max(1, scale.value - 0.5) }
function resetView() { scale.value = 1; translateX.value = 0; translateY.value = 0 }
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col bg-gray-50 relative">
    <!-- Header -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-b shrink-0 z-10">
      <button @click="router.back()" class="text-gray-600">← 뒤로</button>
      <h1 class="text-sm font-bold">상하이 지하철 노선도</h1>
      <button @click="resetView" class="text-xs text-blue-500">초기화</button>
    </div>

    <!-- Map -->
    <div
      class="flex-1 overflow-hidden touch-none select-none"
      @touchstart="onTouchStart"
      @touchmove="onTouchMove"
      @touchend="onTouchEnd"
    >
      <div
        class="w-full h-full"
        :style="{
          transform: `translate3d(${translateX}px, ${translateY}px, 0) scale(${scale})`,
          transformOrigin: 'center center',
          willChange: 'transform',
        }"
      >
        <img
          src="/shanghai-metro-map.jpg"
          alt="상하이 지하철 노선도"
          class="w-full h-full object-contain"
          draggable="false"
        />
      </div>
    </div>

    <!-- Zoom -->
    <div class="absolute bottom-20 right-3 flex flex-col gap-2 z-10">
      <button @click="zoomIn" class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg font-bold active:bg-gray-100">+</button>
      <button @click="zoomOut" class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg font-bold active:bg-gray-100">−</button>
    </div>
  </div>
</template>
