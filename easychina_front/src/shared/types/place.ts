export interface City {
  id: number
  name_ko: string
  name_cn: string
  name_en: string | null
  pinyin: string | null
  latitude: number
  longitude: number
  image_url: string | null
}

export interface Category {
  id: number
  name_ko: string
  name_cn: string | null
  icon: string | null
  color: string | null
}

export interface PlaceImage {
  id: number
  image_url: string
  sort_order: number
  is_primary: boolean
}

export interface Place {
  id: number
  city_id: number
  category_id: number
  name_ko: string
  name_cn: string
  name_en: string | null
  pinyin: string | null
  address_ko: string | null
  address_cn: string
  latitude: number
  longitude: number
  phone: string | null
  business_hours: string | null
  closed_days: string | null
  price_min: number | null
  price_max: number | null
  pay_alipay: boolean
  pay_wechat: boolean
  pay_cash: boolean
  has_english_menu: boolean
  restroom_rating: number | null
  description: string | null
  tips: string | null
  recommendation_score: number
  rating: number | null
  status: 'PUBLIC' | 'PRIVATE' | 'DRAFT'
  view_count: number
  bookmark_count: number
  city?: City
  category?: Category
  images?: PlaceImage[]
  tags?: Tag[]
  is_bookmarked?: boolean
}

export interface Tag {
  id: number
  name: string
}

export interface TipCategory {
  id: number
  name: string
  icon: string | null
}

export interface Tip {
  id: number
  tip_category_id: number
  city_id: number | null
  title: string
  content: string
  image_url: string | null
  tip_category?: TipCategory
  city?: City
}

export interface PhraseCategory {
  id: number
  name: string
  icon: string | null
}

export interface Phrase {
  id: number
  phrase_category_id: number
  text_ko: string
  text_cn: string
  pinyin: string | null
  phrase_category?: PhraseCategory
}

export interface Banner {
  id: number
  city_id: number | null
  content: string
  type: 'INFO' | 'WARNING' | 'URGENT'
  city?: City
}

export interface TravelCourse {
  id: number
  title: string
  start_date: string | null
  end_date: string | null
  memo: string | null
  items?: TravelCourseItem[]
}

export interface TravelCourseItem {
  id: number
  place_id: number
  day_number: number | null
  sort_order: number
  memo: string | null
  place?: Place
}

export interface ApiResponse<T = any> {
  success: boolean
  data: T
  message?: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
