<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Place } from '../../shared/types/place'
import { imageUrl } from '../../shared/utils/image'

const props = defineProps<{ place: Place }>()
const router = useRouter()

const imgSrc = computed(() => {
  const p = props.place as any
  return imageUrl(p.primary_image?.image_url || p.images?.[0]?.image_url)
})

function priceText(): string {
  if (!props.place.price_min && !props.place.price_max) return ''
  if (props.place.price_min && props.place.price_max) {
    return `${(props.place.price_min / 10000).toFixed(0)}-${(props.place.price_max / 10000).toFixed(0)}만원`
  }
  return `~${((props.place.price_max || props.place.price_min || 0) / 10000).toFixed(0)}만원`
}
</script>

<template>
  <div
    @click="router.push(`/places/${place.id}`)"
    class="bg-white rounded-xl overflow-hidden shadow-sm cursor-pointer active:scale-[0.98] transition-transform"
  >
    <div class="aspect-[4/3] bg-gray-100 relative">
      <img
        v-if="imgSrc"
        :src="imgSrc"
        :alt="place.name_ko"
        class="w-full h-full object-cover"
        loading="lazy"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-3xl text-gray-300">
        📷
      </div>
      <span
        v-if="place.category"
        class="absolute top-2 left-2 text-[10px] text-white px-1.5 py-0.5 rounded-full"
        :style="{ backgroundColor: place.category.color || '#3b82f6' }"
      >
        {{ place.category.name_ko }}
      </span>
    </div>
    <div class="p-2.5">
      <h3 class="text-sm font-semibold text-gray-800 truncate">{{ place.name_ko }}</h3>
      <p class="text-[11px] text-gray-400 truncate">{{ place.name_cn }}</p>
      <div class="flex items-center justify-between mt-1.5">
        <span v-if="place.rating" class="text-[11px] text-yellow-500">
          ⭐ {{ place.rating }}
        </span>
        <span v-if="priceText()" class="text-[11px] text-gray-500">
          💰 {{ priceText() }}
        </span>
      </div>
    </div>
  </div>
</template>
