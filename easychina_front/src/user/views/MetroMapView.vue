<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="h-[calc(100vh-56px)] flex flex-col bg-white">
    <!-- Header -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-b shrink-0 z-10">
      <button @click="router.back()" class="text-gray-600">← 뒤로</button>
      <h1 class="text-sm font-bold">상하이 지하철 노선도</h1>
      <div class="w-10"></div>
    </div>

    <!-- PDF Viewer -->
    <iframe
      src="/shanghai-metro-map.pdf"
      class="flex-1 w-full border-0"
      title="상하이 지하철 노선도"
    ></iframe>
  </div>
</template>
