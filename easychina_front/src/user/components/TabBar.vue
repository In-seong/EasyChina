<script setup lang="ts">
interface Tab {
  key: string
  label: string
  icon: string
  route: string
}

defineProps<{
  tabs: Tab[]
  current: string
}>()

const emit = defineEmits<{
  change: [tab: string]
}>()
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50"
       style="padding-bottom: var(--safe-area-bottom)">
    <div class="flex justify-around items-center h-14">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="emit('change', tab.key)"
        class="flex flex-col items-center justify-center flex-1 h-full transition-colors"
        :class="current === tab.key ? 'text-blue-500' : 'text-gray-400'"
      >
        <span class="text-lg">{{ tab.icon }}</span>
        <span class="text-[10px] mt-0.5">{{ tab.label }}</span>
      </button>
    </div>
  </nav>
</template>
