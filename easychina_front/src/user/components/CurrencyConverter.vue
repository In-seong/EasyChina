<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'

const CACHE_KEY = 'easychina_exchange_rate'
const CACHE_DURATION = 60 * 60 * 1000 // 1 hour

const isOpen = ref(false)
const krwAmount = ref('')
const cnyAmount = ref('')
const rate = ref<number | null>(null)
const lastUpdated = ref<string | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const activeField = ref<'krw' | 'cny'>('krw')

interface CachedRate {
  rate: number
  timestamp: number
}

function getCachedRate(): CachedRate | null {
  try {
    const raw = localStorage.getItem(CACHE_KEY)
    if (!raw) return null
    const cached: CachedRate = JSON.parse(raw)
    if (Date.now() - cached.timestamp < CACHE_DURATION) {
      return cached
    }
  } catch {
    // ignore
  }
  return null
}

function setCachedRate(value: number) {
  const data: CachedRate = { rate: value, timestamp: Date.now() }
  localStorage.setItem(CACHE_KEY, JSON.stringify(data))
}

function formatTime(timestamp: number): string {
  const date = new Date(timestamp)
  const h = date.getHours().toString().padStart(2, '0')
  const m = date.getMinutes().toString().padStart(2, '0')
  return `${h}:${m}`
}

async function fetchRate() {
  const cached = getCachedRate()
  if (cached) {
    rate.value = cached.rate
    lastUpdated.value = formatTime(cached.timestamp)
    return
  }

  loading.value = true
  error.value = null
  try {
    const res = await fetch('https://open.er-api.com/v6/latest/CNY')
    const json = await res.json()
    if (json.result === 'success' && json.rates?.KRW) {
      rate.value = json.rates.KRW
      setCachedRate(json.rates.KRW)
      lastUpdated.value = formatTime(Date.now())
    } else {
      throw new Error('Invalid response')
    }
  } catch {
    error.value = '환율 정보를 불러올 수 없습니다'
  } finally {
    loading.value = false
  }
}

function formatNumber(value: string, decimals: number): string {
  const num = parseFloat(value)
  if (isNaN(num)) return ''
  return num.toLocaleString('ko-KR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: decimals,
  })
}

watch(krwAmount, (val) => {
  if (activeField.value !== 'krw' || !rate.value) return
  const num = parseFloat(val.replace(/,/g, ''))
  if (isNaN(num) || val === '') {
    cnyAmount.value = ''
    return
  }
  cnyAmount.value = (num / rate.value).toFixed(2)
})

watch(cnyAmount, (val) => {
  if (activeField.value !== 'cny' || !rate.value) return
  const num = parseFloat(val.replace(/,/g, ''))
  if (isNaN(num) || val === '') {
    krwAmount.value = ''
    return
  }
  krwAmount.value = Math.round(num * rate.value).toString()
})

function onKrwFocus() {
  activeField.value = 'krw'
}

function onCnyFocus() {
  activeField.value = 'cny'
}

function openConverter() {
  isOpen.value = true
  fetchRate()
}

function closeConverter() {
  isOpen.value = false
}

function refreshRate() {
  localStorage.removeItem(CACHE_KEY)
  fetchRate()
}

onMounted(() => {
  // Pre-fetch rate so it is ready when opened
  fetchRate()
})
</script>

<template>
  <!-- Floating trigger button -->
  <button
    v-if="!isOpen"
    @click="openConverter"
    class="fixed left-4 z-[8] w-12 h-12 rounded-full bg-blue-500 text-white shadow-lg flex items-center justify-center text-xl active:scale-95 transition-transform"
    style="bottom: calc(4rem + var(--safe-area-bottom, 0px))"
    aria-label="환율 계산기 열기"
  >
    <span>💱</span>
  </button>

  <!-- Converter popup -->
  <Teleport to="body">
    <!-- Backdrop -->
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/30 z-50"
      @click="closeConverter"
    />

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-full opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-full opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed left-3 right-3 z-50 bg-white rounded-2xl shadow-2xl overflow-hidden"
        style="bottom: calc(4.5rem + var(--safe-area-bottom, 0px))"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-blue-500 text-white">
          <span class="font-semibold text-sm">환율 계산기</span>
          <button
            @click="closeConverter"
            class="w-7 h-7 flex items-center justify-center rounded-full bg-white/20 text-white text-sm"
            aria-label="닫기"
          >
            ✕
          </button>
        </div>

        <div class="p-4 space-y-3">
          <!-- Loading state -->
          <div v-if="loading" class="text-center py-4 text-gray-400 text-sm">
            환율 정보를 불러오는 중...
          </div>

          <!-- Error state -->
          <div v-else-if="error" class="text-center py-4">
            <p class="text-red-400 text-sm mb-2">{{ error }}</p>
            <button
              @click="refreshRate"
              class="text-blue-500 text-sm underline"
            >
              다시 시도
            </button>
          </div>

          <!-- Converter body -->
          <template v-else-if="rate">
            <!-- KRW input -->
            <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-3 py-2.5">
              <div class="flex-shrink-0 text-center">
                <div class="text-lg leading-none">🇰🇷</div>
                <div class="text-[10px] text-gray-400 mt-0.5">KRW</div>
              </div>
              <input
                v-model="krwAmount"
                @focus="onKrwFocus"
                type="text"
                inputmode="decimal"
                placeholder="원화 금액"
                class="flex-1 bg-transparent text-right text-lg font-medium outline-none text-gray-800 placeholder:text-gray-300"
              />
              <span class="text-sm text-gray-400 flex-shrink-0">원</span>
            </div>

            <!-- Swap indicator -->
            <div class="flex justify-center">
              <div class="text-gray-300 text-lg">⇅</div>
            </div>

            <!-- CNY input -->
            <div class="flex items-center gap-3 bg-gray-50 rounded-xl px-3 py-2.5">
              <div class="flex-shrink-0 text-center">
                <div class="text-lg leading-none">🇨🇳</div>
                <div class="text-[10px] text-gray-400 mt-0.5">CNY</div>
              </div>
              <input
                v-model="cnyAmount"
                @focus="onCnyFocus"
                type="text"
                inputmode="decimal"
                placeholder="위안 금액"
                class="flex-1 bg-transparent text-right text-lg font-medium outline-none text-gray-800 placeholder:text-gray-300"
              />
              <span class="text-sm text-gray-400 flex-shrink-0">元</span>
            </div>

            <!-- Rate info -->
            <div class="flex items-center justify-between text-[11px] text-gray-400 px-1">
              <span>1 CNY = {{ formatNumber(rate.toFixed(2), 2) }} KRW</span>
              <div class="flex items-center gap-1.5">
                <span v-if="lastUpdated">{{ lastUpdated }} 기준</span>
                <button
                  @click="refreshRate"
                  class="text-blue-400 active:text-blue-600"
                  aria-label="환율 새로고침"
                >
                  ↻
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
