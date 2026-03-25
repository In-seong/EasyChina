<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../shared/api'
import type { City, Category, PlaceImage } from '../../shared/types/place'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const cities = ref<City[]>([])
const categories = ref<Category[]>([])
const images = ref<PlaceImage[]>([])
const uploading = ref(false)

const form = ref({
  city_id: '', category_id: '',
  name_ko: '', name_cn: '', name_en: '', pinyin: '',
  address_ko: '', address_cn: '',
  latitude: '', longitude: '',
  phone: '', business_hours: '', closed_days: '',
  price_min: '', price_max: '',
  pay_alipay: true, pay_wechat: true, pay_cash: false,
  has_english_menu: false, restroom_rating: '',
  description: '', tips: '',
  recommendation_score: 50, rating: '',
  status: 'DRAFT',
})

async function fetchData() {
  const [c, cat] = await Promise.all([
    api.get('/api/admin/cities'),
    api.get('/api/admin/categories'),
  ])
  cities.value = c.data.data
  categories.value = cat.data.data

  if (isEdit.value) {
    const { data } = await api.get(`/api/admin/places/${route.params.id}`)
    const p = data.data
    images.value = p.images || []
    form.value = {
      city_id: p.city_id, category_id: p.category_id,
      name_ko: p.name_ko, name_cn: p.name_cn, name_en: p.name_en || '', pinyin: p.pinyin || '',
      address_ko: p.address_ko || '', address_cn: p.address_cn,
      latitude: p.latitude, longitude: p.longitude,
      phone: p.phone || '', business_hours: p.business_hours || '', closed_days: p.closed_days || '',
      price_min: p.price_min || '', price_max: p.price_max || '',
      pay_alipay: p.pay_alipay, pay_wechat: p.pay_wechat, pay_cash: p.pay_cash,
      has_english_menu: p.has_english_menu, restroom_rating: p.restroom_rating || '',
      description: p.description || '', tips: p.tips || '',
      recommendation_score: p.recommendation_score, rating: p.rating || '',
      status: p.status,
    }
  }
}

async function save() {
  const payload = { ...form.value }
  if (isEdit.value) {
    await api.put(`/api/admin/places/${route.params.id}`, payload)
  } else {
    const { data } = await api.post('/api/admin/places', payload)
    // 새로 생성된 경우 편집 모드로 전환 (이미지 업로드 가능하도록)
    router.replace(`/places/${data.data.id}/edit`)
    return
  }
  router.push('/places')
}

async function uploadImage(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file || !route.params.id) return
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('image', file)
    formData.append('is_primary', images.value.length === 0 ? '1' : '0')
    const { data } = await api.post(`/api/admin/places/${route.params.id}/images`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    images.value.push(data.data)
  } finally {
    uploading.value = false
    ;(e.target as HTMLInputElement).value = ''
  }
}

async function deleteImage(img: PlaceImage) {
  if (!confirm('이미지를 삭제하시겠습니까?')) return
  await api.delete(`/api/admin/place-images/${img.id}`)
  images.value = images.value.filter(i => i.id !== img.id)
}

onMounted(fetchData)
</script>

<template>
  <div class="p-6 max-w-3xl">
    <h2 class="text-2xl font-bold mb-6">{{ isEdit ? '장소 수정' : '장소 등록' }}</h2>

    <form @submit.prevent="save" class="space-y-6">
      <!-- Basic Info -->
      <fieldset class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <legend class="text-sm font-bold text-gray-700 mb-2">기본 정보</legend>
        <div class="grid grid-cols-2 gap-3">
          <select v-model="form.city_id" required class="border rounded-lg px-3 py-2 text-sm">
            <option value="">도시 선택 *</option>
            <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name_ko }}</option>
          </select>
          <select v-model="form.category_id" required class="border rounded-lg px-3 py-2 text-sm">
            <option value="">카테고리 선택 *</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_ko }}</option>
          </select>
        </div>
        <input v-model="form.name_ko" placeholder="한국어 이름 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.name_cn" placeholder="중국어 이름 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
        <div class="grid grid-cols-2 gap-3">
          <input v-model="form.name_en" placeholder="영어 이름" class="border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.pinyin" placeholder="병음" class="border rounded-lg px-3 py-2 text-sm" />
        </div>
      </fieldset>

      <!-- Location -->
      <fieldset class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <legend class="text-sm font-bold text-gray-700 mb-2">위치 정보</legend>
        <input v-model="form.address_ko" placeholder="한국어 주소" class="w-full border rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.address_cn" placeholder="중국어 주소 *" required class="w-full border rounded-lg px-3 py-2 text-sm" />
        <div class="grid grid-cols-2 gap-3">
          <input v-model="form.latitude" placeholder="위도 (GCJ-02) *" required class="border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.longitude" placeholder="경도 (GCJ-02) *" required class="border rounded-lg px-3 py-2 text-sm" />
        </div>
      </fieldset>

      <!-- Images (edit only) -->
      <fieldset v-if="isEdit" class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <legend class="text-sm font-bold text-gray-700 mb-2">이미지</legend>
        <div class="flex gap-3 flex-wrap">
          <div v-for="img in images" :key="img.id" class="relative w-24 h-24 rounded-lg overflow-hidden border">
            <img :src="img.image_url" class="w-full h-full object-cover" />
            <span v-if="img.is_primary" class="absolute top-1 left-1 bg-blue-500 text-white text-[9px] px-1 rounded">대표</span>
            <button @click="deleteImage(img)" type="button"
                    class="absolute top-1 right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
              x
            </button>
          </div>
          <label class="w-24 h-24 border-2 border-dashed rounded-lg flex items-center justify-center cursor-pointer text-gray-400 hover:text-gray-600">
            <span v-if="uploading" class="text-xs">...</span>
            <span v-else class="text-2xl">+</span>
            <input type="file" accept="image/*" @change="uploadImage" class="hidden" :disabled="uploading" />
          </label>
        </div>
        <p class="text-xs text-gray-400">첫 번째 이미지가 대표 이미지로 설정됩니다. (최대 5MB)</p>
      </fieldset>
      <div v-else class="bg-yellow-50 rounded-xl p-4 text-sm text-yellow-700">
        저장 후 이미지를 업로드할 수 있습니다.
      </div>

      <!-- Operation -->
      <fieldset class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <legend class="text-sm font-bold text-gray-700 mb-2">운영 정보</legend>
        <input v-model="form.phone" placeholder="전화번호" class="w-full border rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.business_hours" placeholder="영업시간 (예: 09:00-22:00)" class="w-full border rounded-lg px-3 py-2 text-sm" />
        <input v-model="form.closed_days" placeholder="휴무일" class="w-full border rounded-lg px-3 py-2 text-sm" />
        <div class="grid grid-cols-2 gap-3">
          <input v-model="form.price_min" type="number" placeholder="최소 가격 (원)" class="border rounded-lg px-3 py-2 text-sm" />
          <input v-model="form.price_max" type="number" placeholder="최대 가격 (원)" class="border rounded-lg px-3 py-2 text-sm" />
        </div>
        <div class="flex gap-4 text-sm">
          <label class="flex items-center gap-1"><input type="checkbox" v-model="form.pay_alipay" /> 알리페이</label>
          <label class="flex items-center gap-1"><input type="checkbox" v-model="form.pay_wechat" /> 위챗페이</label>
          <label class="flex items-center gap-1"><input type="checkbox" v-model="form.pay_cash" /> 현금</label>
          <label class="flex items-center gap-1"><input type="checkbox" v-model="form.has_english_menu" /> 영어메뉴</label>
        </div>
        <select v-model="form.restroom_rating" class="border rounded-lg px-3 py-2 text-sm">
          <option value="">화장실 청결도</option>
          <option v-for="n in 5" :key="n" :value="n">{{ '⭐'.repeat(n) }}</option>
        </select>
      </fieldset>

      <!-- Content -->
      <fieldset class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <legend class="text-sm font-bold text-gray-700 mb-2">콘텐츠</legend>
        <textarea v-model="form.description" placeholder="설명 (한국어)" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        <textarea v-model="form.tips" placeholder="여행 팁" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
      </fieldset>

      <!-- Settings -->
      <fieldset class="bg-white rounded-xl p-5 shadow-sm space-y-3">
        <legend class="text-sm font-bold text-gray-700 mb-2">노출 설정</legend>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="text-xs text-gray-500">추천 점수</label>
            <input v-model="form.recommendation_score" type="number" min="1" max="100" class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="text-xs text-gray-500">별점</label>
            <input v-model="form.rating" type="number" step="0.1" min="1" max="5" class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="text-xs text-gray-500">상태</label>
            <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 text-sm">
              <option value="DRAFT">임시</option>
              <option value="PUBLIC">공개</option>
              <option value="PRIVATE">비공개</option>
            </select>
          </div>
        </div>
      </fieldset>

      <div class="flex justify-end gap-3">
        <button type="button" @click="router.back()" class="px-6 py-2 text-gray-500 text-sm">취소</button>
        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg text-sm">저장</button>
      </div>
    </form>
  </div>
</template>
