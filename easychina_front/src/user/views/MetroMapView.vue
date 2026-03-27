<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const container = ref<HTMLDivElement | null>(null)
const img = ref<HTMLImageElement | null>(null)

const scale = ref(0.3)
const translateX = ref(0)
const translateY = ref(0)
const isDragging = ref(false)

let startX = 0
let startY = 0
let lastTranslateX = 0
let lastTranslateY = 0

// 핀치 줌
let initialPinchDistance = 0
let initialScale = 0.3

function onTouchStart(e: TouchEvent) {
  if (e.touches.length === 2) {
    // 핀치 시작
    initialPinchDistance = getPinchDistance(e)
    initialScale = scale.value
  } else if (e.touches.length === 1) {
    // 드래그 시작
    isDragging.value = true
    startX = e.touches[0].clientX - translateX.value
    startY = e.touches[0].clientY - translateY.value
  }
}

function onTouchMove(e: TouchEvent) {
  e.preventDefault()
  if (e.touches.length === 2) {
    // 핀치 줌
    const dist = getPinchDistance(e)
    const newScale = initialScale * (dist / initialPinchDistance)
    scale.value = Math.max(0.15, Math.min(3, newScale))
  } else if (e.touches.length === 1 && isDragging.value) {
    // 드래그
    translateX.value = e.touches[0].clientX - startX
    translateY.value = e.touches[0].clientY - startY
  }
}

function onTouchEnd() {
  isDragging.value = false
}

// 마우스 (PC)
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

function onMouseUp() {
  isDragging.value = false
}

function onWheel(e: WheelEvent) {
  e.preventDefault()
  const delta = e.deltaY > 0 ? -0.05 : 0.05
  scale.value = Math.max(0.15, Math.min(3, scale.value + delta))
}

function getPinchDistance(e: TouchEvent): number {
  return Math.hypot(
    e.touches[0].clientX - e.touches[1].clientX,
    e.touches[0].clientY - e.touches[1].clientY
  )
}

function zoomIn() {
  scale.value = Math.min(3, scale.value + 0.15)
}

function zoomOut() {
  scale.value = Math.max(0.15, scale.value - 0.15)
}

const IMG_W = 5348
const IMG_H = 7213
const ready = ref(false)

function resetView() {
  if (!container.value) return
  const cw = container.value.clientWidth
  const ch = container.value.clientHeight
  // 화면 너비에 맞추기
  scale.value = cw / IMG_W
  translateX.value = 0
  translateY.value = 0
}

onMounted(async () => {
  await nextTick()
  // 약간 지연 후 컨테이너 크기 확보
  setTimeout(() => {
    resetView()
    ready.value = true
  }, 100)
})
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col bg-gray-100">
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
      <!-- Loading -->
      <div v-if="!ready" class="absolute inset-0 flex items-center justify-center">
        <div class="text-center">
          <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
          <p class="text-xs text-gray-400">노선도 로딩 중...</p>
        </div>
      </div>

      <img
        ref="img"
        src="/shanghai-metro-map.png"
        alt="상하이 지하철 노선도"
        class="absolute origin-top-left"
        :class="[isDragging ? 'cursor-grabbing' : 'cursor-grab', ready ? 'opacity-100' : 'opacity-0']"
        :style="{
          transform: `translate(${translateX}px, ${translateY}px) scale(${scale})`,
          transition: isDragging ? 'none' : 'transform 0.1s ease-out',
        }"
        draggable="false"
      />
    </div>

    <!-- Zoom Controls -->
    <div class="absolute bottom-20 right-3 flex flex-col gap-2 z-10">
      <button
        @click="zoomIn"
        class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg active:bg-gray-100"
      >+</button>
      <button
        @click="zoomOut"
        class="w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-lg active:bg-gray-100"
      >-</button>
    </div>

    <!-- Info -->
    <div class="absolute bottom-20 left-3 z-10">
      <span class="bg-white/90 shadow rounded-full px-3 py-1.5 text-[10px] text-gray-400">
        핀치/스크롤로 확대 · 드래그로 이동
      </span>
    </div>
  </div>
</template>
