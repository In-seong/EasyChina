const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

export function imageUrl(path: string | null | undefined): string | null {
  if (!path) return null
  // 이미 절대 URL이면 그대로
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  // /storage/ 상대 경로면 API URL 붙이기
  return `${API_URL}${path}`
}
