import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // Auth
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { requiresGuest: true },
  },
  // Protected
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/categories',
    name: 'categories',
    component: () => import('@/views/CategoryView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/transactions',
    name: 'transactions',
    component: () => import('@/views/TransactionView.vue'),
    meta: { requiresAuth: true },
  },
  // Redirect root
  {
    path: '/',
    redirect: '/dashboard',
  },
  // 404
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation Guard
router.beforeEach((to, _from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    // Halaman butuh auth tapi belum login → ke login
    return next({ name: 'login' })
  }

  if (to.meta.requiresGuest && token) {
    // Sudah login tapi akses halaman guest → ke dashboard
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
