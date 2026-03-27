<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const offline = ref(!navigator.onLine)

function handleOnline() {
  offline.value = false
}

function handleOffline() {
  offline.value = true
}

onMounted(() => {
  window.addEventListener('online', handleOnline)
  window.addEventListener('offline', handleOffline)
})

onUnmounted(() => {
  window.removeEventListener('online', handleOnline)
  window.removeEventListener('offline', handleOffline)
})
</script>

<template>
  <Transition name="slide">
    <div
      v-if="offline"
      class="bg-yellow-50 border-b border-yellow-200 px-4 py-2.5 flex items-center gap-2"
      role="alert"
      aria-live="polite"
    >
      <span class="text-sm" aria-hidden="true">&#x1F4E1;</span>
      <p class="text-xs font-medium text-yellow-800">
        오프라인 모드 - 저장된 데이터를 표시합니다
      </p>
    </div>
  </Transition>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>
