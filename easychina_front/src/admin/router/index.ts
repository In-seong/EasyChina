import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue') },
    { path: '/dashboard', name: 'dashboard', component: () => import('../views/DashboardView.vue') },
    { path: '/cities', name: 'cities', component: () => import('../views/CityListView.vue') },
    { path: '/categories', name: 'categories', component: () => import('../views/CategoryListView.vue') },
    { path: '/places', name: 'places', component: () => import('../views/PlaceListView.vue') },
    { path: '/places/create', name: 'place-create', component: () => import('../views/PlaceFormView.vue') },
    { path: '/places/:id/edit', name: 'place-edit', component: () => import('../views/PlaceFormView.vue') },
    { path: '/tips', name: 'tips', component: () => import('../views/TipListView.vue') },
    { path: '/phrases', name: 'phrases', component: () => import('../views/PhraseListView.vue') },
    { path: '/banners', name: 'banners', component: () => import('../views/BannerListView.vue') },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (!token && to.name !== 'login') return { name: 'login' }
  if (token && to.name === 'login') return { name: 'dashboard' }
})

export default router
