import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/places',
    },
    {
      path: '/map',
      name: 'map',
      component: () => import('../views/MapView.vue'),
    },
    {
      path: '/places',
      name: 'places',
      component: () => import('../views/PlacesView.vue'),
    },
    {
      path: '/places/:id',
      name: 'place-detail',
      component: () => import('../views/PlaceDetailView.vue'),
    },
    {
      path: '/tips',
      name: 'tips',
      component: () => import('../views/TipsView.vue'),
    },
    {
      path: '/tips/:id',
      name: 'tip-detail',
      component: () => import('../views/TipDetailView.vue'),
    },
    {
      path: '/phrases',
      name: 'phrases',
      component: () => import('../views/PhrasesView.vue'),
    },
    {
      path: '/mypage',
      name: 'mypage',
      component: () => import('../views/MyPageView.vue'),
    },
    {
      path: '/bookmarks',
      name: 'bookmarks',
      component: () => import('../views/BookmarksView.vue'),
    },
    {
      path: '/courses',
      name: 'courses',
      component: () => import('../views/CourseView.vue'),
    },
  ],
})

export default router
