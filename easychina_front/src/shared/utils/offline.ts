import api from '../api'
import type {
  Place,
  Tip,
  TipCategory,
  Phrase,
  PhraseCategory,
  Banner,
  ApiResponse,
  PaginatedResponse,
} from '../types/place'

// ============================================================
// Keys & Constants
// ============================================================
const DB_NAME = 'easychina_offline'
const DB_VERSION = 1
const STORES = {
  places: 'places',
  tips: 'tips',
  tipCategories: 'tipCategories',
  phrases: 'phrases',
  phraseCategories: 'phraseCategories',
  banners: 'banners',
} as const

const LS_PREFIX = 'offline_'
const IMAGE_CACHE_NAME = 'easychina-images'

// ============================================================
// IndexedDB helpers
// ============================================================
function openDB(): Promise<IDBDatabase> {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION)

    request.onupgradeneeded = () => {
      const db = request.result
      for (const store of Object.values(STORES)) {
        if (!db.objectStoreNames.contains(store)) {
          db.createObjectStore(store, { keyPath: 'id' })
        }
      }
    }

    request.onsuccess = () => resolve(request.result)
    request.onerror = () => reject(request.error)
  })
}

async function dbPutAll<T extends { id: number }>(
  storeName: string,
  items: T[],
): Promise<void> {
  const db = await openDB()
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, 'readwrite')
    const store = tx.objectStore(storeName)
    for (const item of items) {
      store.put(item)
    }
    tx.oncomplete = () => resolve()
    tx.onerror = () => reject(tx.error)
  })
}

async function dbGetAll<T>(storeName: string): Promise<T[]> {
  const db = await openDB()
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, 'readonly')
    const store = tx.objectStore(storeName)
    const request = store.getAll()
    request.onsuccess = () => resolve(request.result as T[])
    request.onerror = () => reject(request.error)
  })
}

async function dbClearStore(storeName: string): Promise<void> {
  const db = await openDB()
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, 'readwrite')
    const store = tx.objectStore(storeName)
    const request = store.clear()
    request.onsuccess = () => resolve()
    request.onerror = () => reject(request.error)
  })
}

// ============================================================
// localStorage helpers
// ============================================================
function lsSet(key: string, value: unknown) {
  localStorage.setItem(LS_PREFIX + key, JSON.stringify(value))
}

function lsGet<T>(key: string, fallback: T): T {
  try {
    const raw = localStorage.getItem(LS_PREFIX + key)
    return raw ? (JSON.parse(raw) as T) : fallback
  } catch {
    return fallback
  }
}

function lsRemove(key: string) {
  localStorage.removeItem(LS_PREFIX + key)
}

// ============================================================
// Image caching (Cache API)
// ============================================================
async function cacheImages(
  urls: string[],
  onProgress?: (cached: number, total: number) => void,
): Promise<void> {
  if (!('caches' in window) || urls.length === 0) return

  const cache = await caches.open(IMAGE_CACHE_NAME)
  let cached = 0

  for (const url of urls) {
    try {
      const existing = await cache.match(url)
      if (!existing) {
        const response = await fetch(url, { mode: 'cors' })
        if (response.ok) {
          await cache.put(url, response)
        }
      }
    } catch {
      // skip failed images silently
    }
    cached++
    onProgress?.(cached, urls.length)
  }
}

async function clearImageCache(): Promise<void> {
  if ('caches' in window) {
    await caches.delete(IMAGE_CACHE_NAME)
  }
}

// ============================================================
// Collect image URLs from data
// ============================================================
function collectImageUrls(
  places: Place[],
  tips: Tip[],
  banners: Banner[],
): string[] {
  const urls: string[] = []

  for (const place of places) {
    if (place.images) {
      for (const img of place.images) {
        if (img.image_url) urls.push(img.image_url)
      }
    }
  }

  for (const tip of tips) {
    if (tip.image_url) urls.push(tip.image_url)
  }

  return [...new Set(urls)]
}

// ============================================================
// Download progress tracking
// ============================================================
export interface DownloadProgress {
  stage: string
  percent: number
}

export type ProgressCallback = (progress: DownloadProgress) => void

// ============================================================
// Public API
// ============================================================

/**
 * Download all offline data for a given city.
 * Stores places, tip categories + tips, phrase categories + phrases,
 * banners, and caches referenced images.
 */
export async function downloadCityData(
  cityId: number,
  onProgress?: ProgressCallback,
): Promise<void> {
  const report = (stage: string, percent: number) =>
    onProgress?.({ stage, percent })

  try {
    // ---- 0. Clear existing data ----
    for (const store of Object.values(STORES)) {
      await dbClearStore(store)
    }

    // ---- 1. Places (paginated, fetch all) ----
    report('장소 데이터 다운로드 중...', 5)
    const allPlaces: Place[] = []
    let page = 1
    let lastPage = 1
    do {
      const { data } = await api.get<ApiResponse<PaginatedResponse<Place>>>(
        '/api/user/places',
        { params: { city_id: cityId, per_page: 200, page } },
      )
      allPlaces.push(...data.data.data)
      lastPage = data.data.last_page
      page++
    } while (page <= lastPage)
    await dbPutAll(STORES.places, allPlaces)
    report('장소 저장 완료', 20)

    // ---- 2. Tip Categories + Tips ----
    report('여행수첩 다운로드 중...', 25)
    const { data: tipCatRes } = await api.get<ApiResponse<TipCategory[]>>(
      '/api/user/tip-categories',
    )
    const tipCategories = tipCatRes.data
    await dbPutAll(STORES.tipCategories, tipCategories)

    const allTips: Tip[] = []
    for (const cat of tipCategories) {
      try {
        const { data: tipRes } = await api.get<ApiResponse<{ category: TipCategory; tips: Tip[] }>>(
          `/api/user/tip-categories/${cat.id}`,
        )
        if (tipRes.data?.tips && Array.isArray(tipRes.data.tips)) {
          allTips.push(...tipRes.data.tips)
        }
      } catch {
        // skip
      }
    }
    await dbPutAll(STORES.tips, allTips)
    report('여행수첩 저장 완료', 40)

    // ---- 3. Phrase Categories + Phrases ----
    report('번역 문구 다운로드 중...', 45)
    const { data: phraseCatRes } = await api.get<ApiResponse<PhraseCategory[]>>(
      '/api/user/phrase-categories',
    )
    const phraseCategories = phraseCatRes.data
    await dbPutAll(STORES.phraseCategories, phraseCategories)

    const allPhrases: Phrase[] = []
    for (const cat of phraseCategories) {
      const { data: phraseRes } = await api.get<ApiResponse<Phrase[]>>(
        '/api/user/phrases',
        { params: { phrase_category_id: cat.id } },
      )
      allPhrases.push(...phraseRes.data)
    }
    await dbPutAll(STORES.phrases, allPhrases)
    report('번역 문구 저장 완료', 60)

    // ---- 4. Banners ----
    report('배너 다운로드 중...', 65)
    const { data: bannerRes } = await api.get<ApiResponse<Banner[]>>(
      '/api/user/banners',
    )
    await dbPutAll(STORES.banners, bannerRes.data)
    report('배너 저장 완료', 70)

    // ---- 5. Cache images ----
    report('이미지 캐싱 중...', 75)
    const imageUrls = collectImageUrls(allPlaces, allTips, bannerRes.data)
    await cacheImages(imageUrls, (cached, total) => {
      const imgPercent = 75 + Math.round((cached / total) * 20)
      report(`이미지 캐싱 중... (${cached}/${total})`, imgPercent)
    })

    // ---- 6. Save metadata ----
    const meta = {
      cityId,
      downloadedAt: new Date().toISOString(),
      placesCount: allPlaces.length,
      tipsCount: allTips.length,
      phrasesCount: allPhrases.length,
      bannersCount: bannerRes.data.length,
      imagesCount: imageUrls.length,
    }
    lsSet(`city_${cityId}`, meta)

    report('다운로드 완료!', 100)
  } catch (error) {
    report('다운로드 실패', -1)
    throw error
  }
}

/**
 * Get cached places for a given city.
 */
export async function getCachedPlaces(cityId: number): Promise<Place[]> {
  const all = await dbGetAll<Place>(STORES.places)
  return all.filter((p) => p.city_id === cityId)
}

/**
 * Get cached tips.
 */
export async function getCachedTips(): Promise<Tip[]> {
  return dbGetAll<Tip>(STORES.tips)
}

/**
 * Get cached tip categories.
 */
export async function getCachedTipCategories(): Promise<TipCategory[]> {
  return dbGetAll<TipCategory>(STORES.tipCategories)
}

/**
 * Get cached phrases.
 */
export async function getCachedPhrases(): Promise<Phrase[]> {
  return dbGetAll<Phrase>(STORES.phrases)
}

/**
 * Get cached phrase categories.
 */
export async function getCachedPhraseCategories(): Promise<PhraseCategory[]> {
  return dbGetAll<PhraseCategory>(STORES.phraseCategories)
}

/**
 * Get cached banners.
 */
export async function getCachedBanners(): Promise<Banner[]> {
  return dbGetAll<Banner>(STORES.banners)
}

/**
 * Check if the browser is offline.
 */
export function isOffline(): boolean {
  return !navigator.onLine
}

/**
 * Get download metadata for a city (null if not downloaded).
 */
export function getCityDownloadMeta(cityId: number) {
  return lsGet<{
    cityId: number
    downloadedAt: string
    placesCount: number
    tipsCount: number
    phrasesCount: number
    bannersCount: number
    imagesCount: number
  } | null>(`city_${cityId}`, null)
}

/**
 * Estimate total cache size in bytes.
 * Uses a rough estimate since exact IndexedDB size is hard to query.
 */
export async function getCacheSize(): Promise<number> {
  if ('storage' in navigator && 'estimate' in navigator.storage) {
    const estimate = await navigator.storage.estimate()
    return estimate.usage || 0
  }
  return 0
}

/**
 * Clear all cached data for a given city.
 */
export async function clearCityCache(cityId: number): Promise<void> {
  // Clear all IndexedDB stores
  for (const store of Object.values(STORES)) {
    await dbClearStore(store)
  }

  // Clear image cache
  await clearImageCache()

  // Remove metadata
  lsRemove(`city_${cityId}`)
}

/**
 * Format bytes into human-readable string.
 */
export function formatBytes(bytes: number): string {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}
