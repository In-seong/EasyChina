<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const container = ref<HTMLDivElement | null>(null)

const scale = ref(1)
const translateX = ref(0)
const translateY = ref(0)
const isDragging = ref(false)

let startX = 0
let startY = 0

// 핀치 줌
let initialPinchDistance = 0
let initialScale = 1

function onTouchStart(e: TouchEvent) {
  if (e.touches.length === 2) {
    initialPinchDistance = getPinchDistance(e)
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
    const dist = getPinchDistance(e)
    scale.value = Math.max(1, Math.min(8, initialScale * (dist / initialPinchDistance)))
  } else if (e.touches.length === 1 && isDragging.value) {
    translateX.value = e.touches[0].clientX - startX
    translateY.value = e.touches[0].clientY - startY
  }
}

function onTouchEnd() {
  isDragging.value = false
}

function onMouseDown(e: MouseEvent) {
  isDragging.value = true
  startX = e.clientX - translateX.value
  startY = e.clientY - translateY.value
}

function onMouseMove(e: MouseEvent) {
  if (!isDragging.value) return
  translateX.value = e.clientX - startX
  translateY.value = e.clientY - startY
}

function onMouseUp() { isDragging.value = false }

function onWheel(e: WheelEvent) {
  e.preventDefault()
  const delta = e.deltaY > 0 ? -0.2 : 0.2
  scale.value = Math.max(1, Math.min(8, scale.value + delta))
}

function getPinchDistance(e: TouchEvent): number {
  return Math.hypot(
    e.touches[0].clientX - e.touches[1].clientX,
    e.touches[0].clientY - e.touches[1].clientY
  )
}

function zoomIn() { scale.value = Math.min(8, scale.value + 0.5) }
function zoomOut() { scale.value = Math.max(1, scale.value - 0.5) }
function resetView() { scale.value = 1; translateX.value = 0; translateY.value = 0 }
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-b shrink-0 z-10">
      <button @click="router.back()" class="text-gray-600">← 뒤로</button>
      <h1 class="text-sm font-bold">상하이 지하철 노선도</h1>
      <button @click="resetView" class="text-xs text-blue-500">초기화</button>
    </div>

    <!-- Map Viewer -->
    <div
      ref="container"
      class="flex-1 overflow-hidden relative touch-none select-none"
      @touchstart="onTouchStart"
      @touchmove="onTouchMove"
      @touchend="onTouchEnd"
      @mousedown="onMouseDown"
      @mousemove="onMouseMove"
      @mouseup="onMouseUp"
      @mouseleave="onMouseUp"
      @wheel="onWheel"
    >
      <div
        class="w-full h-full"
        :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
        :style="{
          transform: `translate(${translateX}px, ${translateY}px) scale(${scale})`,
          transformOrigin: 'center center',
          transition: isDragging ? 'none' : 'transform 0.15s ease-out',
        }"
      >
        <img
          src="/shanghai-metro-map.png"
          alt="상하이 지하철 노선도"
          class="w-full h-full object-contain"
          draggable="false"
        />
      </div>
    </div>

    <!-- Zoom Controls -->
    <div class="absolute bottom-20 right-3 flex flex-col gap-2 z-10">
      <button @click="zoomIn" class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg font-bold active:bg-gray-100">+</button>
      <button @click="zoomOut" class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg font-bold active:bg-gray-100">−</button>
    </div>

    <!-- Hint -->
    <div class="absolute bottom-20 left-3 z-10">
      <span class="bg-white/90 shadow rounded-full px-3 py-1.5 text-[10px] text-gray-400">
        핀치로 확대 · 드래그로 이동
      </span>
    </div>
  </div>
</template>
