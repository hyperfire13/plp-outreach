import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Users from '../views/Users.vue'
import Colleges from '../views/Colleges.vue'

const routes = [
  { path: '/login', component: Login },
  {
    path: '/',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    component: Users,
    meta: { requiresAuth: true, roles: ['super_admin','college_admin'] }
  },
  {
    path: '/colleges',
    component: Colleges,
    meta: { requiresAuth: true }
  },
{
    path: '/outreach-programs',
    name: 'outreach-programs',
    component: () => import('../views/OutreachPrograms.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// ROUTE GUARD
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.roles && !to.meta.roles.includes(auth.role)) {
    return next('/')
  }

  next()
})

export default router
