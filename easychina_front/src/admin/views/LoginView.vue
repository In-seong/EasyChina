<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../shared/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function login() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/api/admin/login', {
      email: email.value,
      password: password.value,
    })
    localStorage.setItem('token', data.data.token)
    router.push('/dashboard')
    window.location.reload()
  } catch (e: any) {
    error.value = e.response?.data?.message || '로그인 실패'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-sm">
      <h1 class="text-xl font-bold text-center mb-6">EasyChina Admin</h1>
      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">이메일</label>
          <input v-model="email" type="email" required
                 class="w-full border rounded-lg px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">비밀번호</label>
          <input v-model="password" type="password" required
                 class="w-full border rounded-lg px-3 py-2 text-sm" />
        </div>
        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-500 text-white py-2.5 rounded-lg font-medium hover:bg-blue-600 disabled:opacity-50"
        >
          {{ loading ? '로그인 중...' : '로그인' }}
        </button>
      </form>
    </div>
  </div>
</template>
