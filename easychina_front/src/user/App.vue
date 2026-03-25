<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import TabBar from './components/TabBar.vue'

const router = useRouter()
const currentTab = ref('places')

const tabs = [
  { key: 'map', label: '지도', icon: '📍', route: '/map' },
  { key: 'places', label: '여행지', icon: '🗂', route: '/places' },
  { key: 'tips', label: '여행수첩', icon: '📋', route: '/tips' },
  { key: 'phrases', label: '번역', icon: '💬', route: '/phrases' },
  { key: 'mypage', label: 'MY', icon: '👤', route: '/mypage' },
]

function onTabChange(tab: string) {
  currentTab.value = tab
  const found = tabs.find(t => t.key === tab)
  if (found) router.push(found.route)
}

onMounted(() => {
  const path = router.currentRoute.value.path
  const found = tabs.find(t => t.route === path)
  if (found) currentTab.value = found.key
})
</script>

<template>
  <div class="min-h-screen pb-16">
    <router-view />
    <TabBar :tabs="tabs" :current="currentTab" @change="onTabChange" />
  </div>
</template>
