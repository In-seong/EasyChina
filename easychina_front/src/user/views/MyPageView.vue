<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../shared/composables/useAuth'
import { useBookmark } from '../../shared/composables/useBookmark'

const router = useRouter()
const { user, isLoggedIn, register, fetchMe, logout } = useAuth()
const { loadBookmarks } = useBookmark()

const nickname = ref('')
const registering = ref(false)
const showRegister = ref(false)

const menuItems = [
  { icon: '🔖', label: '북마크한 장소', route: '/bookmarks', auth: true },
  { icon: '🗺', label: '나의 여행 코스', route: '/courses', auth: true },
  { icon: '📥', label: '오프라인 데이터', route: '/offline', auth: false },
]

async function doRegister() {
  if (!nickname.value.trim()) return
  registering.value = true
  try {
    await register(nickname.value.trim())
    showRegister.value = false
    loadBookmarks()
  } catch (e) {
    alert('가입에 실패했습니다. 다시 시도해주세요.')
  } finally {
    registering.value = false
  }
}

function handleMenuClick(item: typeof menuItems[0]) {
  if (item.auth && !isLoggedIn.value) {
    showRegister.value = true
    return
  }
  router.push(item.route)
}

function doLogout() {
  if (confirm('로그아웃 하시겠습니까?')) {
    logout()
  }
}

onMounted(() => {
  if (isLoggedIn.value) {
    fetchMe()
    loadBookmarks()
  }
})
</script>

<template>
  <div class="px-4 pt-4 pb-20">
    <h1 class="text-xl font-bold mb-4">마이페이지</h1>

    <!-- 로그인 상태 -->
    <div v-if="isLoggedIn && user" class="bg-white rounded-xl p-4 shadow-sm mb-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-lg">
          {{ user.nickname?.charAt(0) || '?' }}
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-800">{{ user.nickname }}</p>
          <p class="text-xs text-gray-400">여행자</p>
        </div>
      </div>
      <button @click="doLogout" class="text-xs text-gray-400 active:text-gray-600">로그아웃</button>
    </div>

    <!-- 비로그인 상태 -->
    <div v-else class="bg-blue-50 rounded-xl p-4 shadow-sm mb-4">
      <p class="text-sm font-medium text-blue-800 mb-1">닉네임을 등록하고 더 많은 기능을 사용하세요</p>
      <p class="text-xs text-blue-500 mb-3">북마크, 여행 코스 저장이 가능합니다</p>
      <button
        @click="showRegister = true"
        class="w-full py-2.5 bg-blue-500 text-white rounded-lg text-sm font-medium active:bg-blue-600"
      >
        닉네임으로 시작하기
      </button>
    </div>

    <!-- 메뉴 -->
    <div class="space-y-2">
      <button
        v-for="item in menuItems"
        :key="item.route"
        @click="handleMenuClick(item)"
        class="w-full bg-white rounded-xl p-4 flex items-center justify-between shadow-sm active:bg-gray-50"
      >
        <div class="flex items-center gap-3">
          <span class="text-xl">{{ item.icon }}</span>
          <span class="text-sm font-medium text-gray-800">{{ item.label }}</span>
        </div>
        <span class="text-gray-400 text-sm">›</span>
      </button>
    </div>

    <div class="mt-6">
      <div class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-xs text-gray-400">앱 버전 1.0.0</p>
      </div>
    </div>

    <!-- 닉네임 등록 모달 -->
    <Teleport to="body">
      <div v-if="showRegister" class="fixed inset-0 z-50 bg-black/30 flex items-end justify-center">
        <div class="bg-white rounded-t-2xl w-full max-w-lg p-6" @click.stop>
          <h3 class="text-lg font-bold mb-2">닉네임 등록</h3>
          <p class="text-xs text-gray-400 mb-4">여행에서 사용할 닉네임을 입력해주세요</p>
          <form @submit.prevent="doRegister">
            <input
              v-model="nickname"
              placeholder="닉네임 (2~20자)"
              maxlength="20"
              required
              class="w-full border rounded-lg px-4 py-3 text-sm mb-3"
              autofocus
            />
            <div class="flex gap-2">
              <button type="button" @click="showRegister = false" class="flex-1 py-3 rounded-lg text-sm text-gray-500 bg-gray-100">취소</button>
              <button
                type="submit"
                :disabled="registering || nickname.trim().length < 2"
                class="flex-1 py-3 rounded-lg text-sm text-white bg-blue-500 disabled:opacity-50"
              >
                {{ registering ? '등록 중...' : '시작하기' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>
