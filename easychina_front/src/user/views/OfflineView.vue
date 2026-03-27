<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  downloadCityData,
  clearCityCache,
  getCityDownloadMeta,
  getCacheSize,
  formatBytes,
} from '../../shared/utils/offline'

const router = useRouter()

interface CityOption {
  id: number
  nameKo: string
  nameCn: string
  available: boolean
}

const cities: CityOption[] = [
  { id: 2, nameKo: '상하이', nameCn: '上海', available: true },
  { id: 1, nameKo: '베이징', nameCn: '北京', available: false },
  { id: 3, nameKo: '광저우', nameCn: '广州', available: false },
  { id: 4, nameKo: '청두', nameCn: '成都', available: false },
]

const downloading = ref(false)
const downloadingCityId = ref<number | null>(null)
const progress = ref(0)
const progressStage = ref('')
const error = ref('')
const cacheSize = ref(0)

// Track which cities have been downloaded (reactive)
const downloadedCities = ref<Map<number, {
  downloadedAt: string
  placesCount: number
  tipsCount: number
  phrasesCount: number
}>>(new Map())

function loadDownloadStatus() {
  for (const city of cities) {
    const meta = getCityDownloadMeta(city.id)
    if (meta) {
      downloadedCities.value.set(city.id, {
        downloadedAt: meta.downloadedAt,
        placesCount: meta.placesCount,
        tipsCount: meta.tipsCount,
        phrasesCount: meta.phrasesCount,
      })
    } else {
      downloadedCities.value.delete(city.id)
    }
  }
  // Force reactivity
  downloadedCities.value = new Map(downloadedCities.value)
}

async function loadCacheSize() {
  cacheSize.value = await getCacheSize()
}

async function startDownload(cityId: number) {
  if (downloading.value) return

  downloading.value = true
  downloadingCityId.value = cityId
  progress.value = 0
  progressStage.value = '준비 중...'
  error.value = ''

  try {
    await downloadCityData(cityId, (p) => {
      progress.value = Math.max(0, p.percent)
      progressStage.value = p.stage
    })
    loadDownloadStatus()
    await loadCacheSize()
  } catch (e) {
    error.value = '다운로드에 실패했습니다. 네트워크 연결을 확인해주세요.'
  } finally {
    downloading.value = false
    downloadingCityId.value = null
  }
}

async function deleteCache(cityId: number) {
  if (!confirm('저장된 오프라인 데이터를 삭제하시겠습니까?')) return

  try {
    await clearCityCache(cityId)
    loadDownloadStatus()
    await loadCacheSize()
  } catch {
    alert('삭제에 실패했습니다.')
  }
}

function formatDate(isoString: string): string {
  const d = new Date(isoString)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  const hour = String(d.getHours()).padStart(2, '0')
  const min = String(d.getMinutes()).padStart(2, '0')
  return `${year}.${month}.${day} ${hour}:${min}`
}

onMounted(() => {
  loadDownloadStatus()
  loadCacheSize()
})
</script>

<template>
  <div class="px-4 pt-4 pb-20">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
      <button
        @click="router.back()"
        class="text-lg text-gray-600 active:text-gray-900"
        aria-label="뒤로가기"
      >
        &#x2190;
      </button>
      <h1 class="text-xl font-bold">오프라인 데이터</h1>
    </div>

    <!-- Description -->
    <div class="bg-blue-50 rounded-xl p-4 mb-5">
      <p class="text-sm text-blue-800 font-medium mb-1">
        인터넷 없이 여행 정보를 확인하세요
      </p>
      <p class="text-xs text-blue-600">
        도시 데이터를 미리 다운로드하면 오프라인에서도 장소, 여행수첩, 번역 문구를 볼 수 있습니다.
      </p>
    </div>

    <!-- Storage info -->
    <div v-if="cacheSize > 0" class="bg-gray-50 rounded-xl p-3 mb-4 flex items-center justify-between">
      <span class="text-xs text-gray-500">사용 중인 저장 공간</span>
      <span class="text-xs font-semibold text-gray-700">{{ formatBytes(cacheSize) }}</span>
    </div>

    <!-- Error -->
    <div
      v-if="error"
      class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4"
      role="alert"
    >
      <p class="text-sm text-red-700">{{ error }}</p>
    </div>

    <!-- City List -->
    <div class="space-y-3">
      <div
        v-for="city in cities"
        :key="city.id"
        class="bg-white rounded-xl shadow-sm overflow-hidden"
      >
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-900">
                {{ city.nameKo }}
                <span class="text-gray-400 font-normal ml-1">{{ city.nameCn }}</span>
              </p>
            </div>

            <!-- Not available -->
            <span
              v-if="!city.available"
              class="text-xs text-gray-400 bg-gray-100 px-3 py-1.5 rounded-lg"
            >
              준비 중
            </span>

            <!-- Downloading -->
            <div v-else-if="downloading && downloadingCityId === city.id" class="text-right">
              <span class="text-xs text-blue-500 font-medium">다운로드 중...</span>
            </div>

            <!-- Downloaded -->
            <button
              v-else-if="downloadedCities.has(city.id)"
              @click="deleteCache(city.id)"
              class="text-xs text-red-500 bg-red-50 px-3 py-1.5 rounded-lg active:bg-red-100"
            >
              삭제
            </button>

            <!-- Not downloaded -->
            <button
              v-else
              @click="startDownload(city.id)"
              class="text-xs text-white bg-blue-500 px-4 py-1.5 rounded-lg active:bg-blue-600 font-medium"
            >
              다운로드
            </button>
          </div>

          <!-- Progress bar -->
          <div v-if="downloading && downloadingCityId === city.id" class="mt-3">
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
              <div
                class="h-full bg-blue-500 rounded-full transition-all duration-300"
                :style="{ width: Math.max(progress, 0) + '%' }"
              ></div>
            </div>
            <p class="text-xs text-gray-500 mt-1.5">{{ progressStage }}</p>
          </div>

          <!-- Downloaded info -->
          <div v-if="downloadedCities.has(city.id) && !(downloading && downloadingCityId === city.id)" class="mt-3 bg-green-50 rounded-lg p-3">
            <div class="flex items-center gap-1.5 mb-1.5">
              <span class="text-green-500 text-xs">&#x2705;</span>
              <span class="text-xs font-medium text-green-700">데이터 저장됨</span>
            </div>
            <div class="text-xs text-green-600 space-y-0.5">
              <p>
                장소 {{ downloadedCities.get(city.id)!.placesCount }}개,
                여행수첩 {{ downloadedCities.get(city.id)!.tipsCount }}개,
                번역 {{ downloadedCities.get(city.id)!.phrasesCount }}개
              </p>
              <p class="text-green-500">
                마지막 다운로드: {{ formatDate(downloadedCities.get(city.id)!.downloadedAt) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info note -->
    <div class="mt-6 px-1">
      <p class="text-xs text-gray-400 leading-relaxed">
        * 오프라인 데이터는 기기에 저장되며, 브라우저 데이터를 삭제하면 함께 삭제됩니다.
        <br />
        * 최신 정보를 위해 인터넷 연결 시 데이터를 다시 다운로드하는 것을 권장합니다.
      </p>
    </div>
  </div>
</template>
