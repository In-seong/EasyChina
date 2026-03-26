import { ref } from 'vue'
import api from '../api'
import { useAuth } from './useAuth'

const bookmarkedIds = ref<Set<number>>(new Set())
const loaded = ref(false)

export function useBookmark() {
  const { isLoggedIn } = useAuth()

  async function loadBookmarks() {
    if (!isLoggedIn.value || loaded.value) return
    try {
      const { data } = await api.get('/api/user/bookmarks', { params: { per_page: 100 } })
      const ids = (data.data.data || data.data).map((p: any) => p.id)
      bookmarkedIds.value = new Set(ids)
      loaded.value = true
    } catch {
      // 실패 시 무시
    }
  }

  function isBookmarked(placeId: number): boolean {
    return bookmarkedIds.value.has(placeId)
  }

  async function toggleBookmark(placeId: number): Promise<boolean> {
    if (!isLoggedIn.value) return false
    try {
      const { data } = await api.post(`/api/user/bookmarks/${placeId}`)
      if (data.data.bookmarked) {
        bookmarkedIds.value.add(placeId)
      } else {
        bookmarkedIds.value.delete(placeId)
      }
      return data.data.bookmarked
    } catch {
      return false
    }
  }

  return { bookmarkedIds, isBookmarked, toggleBookmark, loadBookmarks }
}
