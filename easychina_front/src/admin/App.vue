<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../shared/api'

const router = useRouter()
const route = useRoute()
const isLoggedIn = ref(!!localStorage.getItem('token'))
const adminName = ref('')

const menuItems = [
  { icon: '📊', label: '대시보드', route: '/dashboard' },
  { icon: '🏙', label: '도시 관리', route: '/cities' },
  { icon: '📂', label: '카테고리 관리', route: '/categories' },
  { icon: '📍', label: '장소 관리', route: '/places' },
  { icon: '📝', label: '여행팁 관리', route: '/tips' },
  { icon: '💬', label: '번역카드 관리', route: '/phrases' },
  { icon: '🔔', label: '배너 관리', route: '/banners' },
]

const currentPath = computed(() => route.path)

async function logout() {
  localStorage.removeItem('token')
  isLoggedIn.value = false
  router.push('/login')
}

onMounted(async () => {
  if (isLoggedIn.value) {
    try {
      const { data } = await api.get('/api/admin/me')
      adminName.value = data.data.name
    } catch {
      logout()
    }
  }
})
</script>

<template>
  <div v-if="!isLoggedIn">
    <router-view />
  </div>
  <div v-else class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-60 bg-gray-900 text-white flex flex-col shrink-0">
      <div class="p-4 border-b border-gray-700">
        <h1 class="text-lg font-bold">EasyChina Admin</h1>
        <p class="text-xs text-gray-400 mt-1">{{ adminName }}</p>
      </div>
      <nav class="flex-1 py-2">
        <router-link
          v-for="item in menuItems"
          :key="item.route"
          :to="item.route"
          class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-800 transition-colors"
          :class="currentPath.startsWith(item.route) ? 'bg-gray-800 text-white' : 'text-gray-300'"
        >
          <span>{{ item.icon }}</span>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>
      <div class="p-4 border-t border-gray-700">
        <button @click="logout" class="text-sm text-gray-400 hover:text-white">로그아웃</button>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 overflow-auto">
      <router-view />
    </main>
  </div>
</template>
