<script setup lang="ts">
import { openMap, isInApp } from '../../shared/utils/bridge'
import { onMounted } from 'vue'

onMounted(() => {
  if (isInApp()) {
    openMap()
  }
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen px-6 text-center">
    <template v-if="isInApp()">
      <p class="text-gray-500">지도를 불러오는 중...</p>
    </template>
    <template v-else>
      <div class="text-6xl mb-4">🗺</div>
      <h2 class="text-xl font-bold text-gray-800 mb-2">지도 기능</h2>
      <p class="text-gray-500 text-sm">
        지도는 앱에서만 사용할 수 있습니다.<br/>
        앱을 설치해 주세요.
      </p>
    </template>
  </div>
</template>
