<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../shared/api'
import type { TravelCourse } from '../../shared/types/place'

const router = useRouter()
const courses = ref<TravelCourse[]>([])
const showForm = ref(false)
const form = ref({ title: '', start_date: '', end_date: '', memo: '' })

async function fetchCourses() {
  const token = localStorage.getItem('token')
  if (!token) return

  const { data } = await api.get('/api/user/travel-courses')
  courses.value = data.data
}

async function createCourse() {
  await api.post('/api/user/travel-courses', {
    ...form.value,
    start_date: form.value.start_date || null,
    end_date: form.value.end_date || null,
    memo: form.value.memo || null,
  })
  showForm.value = false
  form.value = { title: '', start_date: '', end_date: '', memo: '' }
  fetchCourses()
}

async function removeCourse(id: number) {
  if (!confirm('삭제하시겠습니까?')) return
  await api.delete(`/api/user/travel-courses/${id}`)
  fetchCourses()
}

function formatDate(d: string | null) {
  if (!d) return ''
  return d.substring(0, 10)
}

onMounted(fetchCourses)
</script>

<template>
  <div class="pb-4">
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur px-4 py-3 border-b flex justify-between items-center">
      <button @click="router.back()" class="text-gray-600">← 나의 여행 코스</button>
      <button @click="showForm = true" class="text-blue-500 text-sm font-medium">+ 새 코스</button>
    </div>

    <div v-if="courses.length === 0 && !showForm" class="text-center py-12">
      <div class="text-4xl mb-2">🗺</div>
      <p class="text-gray-400 text-sm">등록된 여행 코스가 없습니다</p>
      <button @click="showForm = true" class="mt-4 text-blue-500 text-sm">+ 새 코스 만들기</button>
    </div>

    <div class="px-4 pt-4 space-y-3">
      <div v-for="course in courses" :key="course.id" class="bg-white rounded-xl p-4 shadow-sm active:bg-gray-50">
        <div class="flex justify-between items-start">
          <div class="flex-1" @click="router.push(`/courses/${course.id}`)">
            <h3 class="font-semibold text-gray-800">{{ course.title }}</h3>
            <p v-if="course.start_date" class="text-xs text-gray-400 mt-1">
              {{ formatDate(course.start_date) }}
              <span v-if="course.end_date"> ~ {{ formatDate(course.end_date) }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-0.5">장소 {{ (course as any).items_count || 0 }}개</p>
          </div>
          <button @click.stop="removeCourse(course.id)" class="text-red-400 text-xs">삭제</button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-50 bg-black/30 flex items-end justify-center">
        <div class="bg-white rounded-t-2xl w-full max-w-lg p-6" @click.stop>
          <h3 class="text-lg font-bold mb-4">새 여행 코스</h3>
          <form @submit.prevent="createCourse" class="space-y-3">
            <input v-model="form.title" placeholder="코스 이름 (예: 베이징 3박4일) *" required
                   class="w-full border rounded-lg px-3 py-2.5 text-sm" />
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-xs text-gray-500">시작일</label>
                <input v-model="form.start_date" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
              <div>
                <label class="text-xs text-gray-500">종료일</label>
                <input v-model="form.end_date" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
            </div>
            <textarea v-model="form.memo" placeholder="메모" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            <div class="flex gap-2">
              <button type="button" @click="showForm = false" class="flex-1 py-2.5 rounded-lg text-sm text-gray-500 bg-gray-100">취소</button>
              <button type="submit" class="flex-1 py-2.5 rounded-lg text-sm text-white bg-blue-500">만들기</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>
