import { ref, computed } from 'vue'
import api from '../api'

const user = ref<any>(null)
const token = ref<string | null>(localStorage.getItem('token'))

export function useAuth() {
  const isLoggedIn = computed(() => !!token.value)

  async function register(nickname: string) {
    const { data } = await api.post('/api/user/register', { nickname })
    token.value = data.data.token
    user.value = data.data.user
    localStorage.setItem('token', data.data.token)
    return data.data
  }

  async function fetchMe() {
    if (!token.value) return null
    try {
      const { data } = await api.get('/api/user/me')
      user.value = data.data
      return data.data
    } catch {
      logout()
      return null
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  return { user, token, isLoggedIn, register, fetchMe, logout }
}
