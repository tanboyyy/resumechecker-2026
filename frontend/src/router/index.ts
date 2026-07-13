import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/pages/HomePage.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/LoginPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/pages/DashboardPage.vue'),
      meta: { auth: true },
    },
    {
      path: '/resumes',
      name: 'resumes',
      component: () => import('@/pages/ResumesPage.vue'),
      meta: { auth: true },
    },
    {
      path: '/resumes/:id',
      name: 'resume',
      component: () => import('@/pages/ResumeDetailPage.vue'),
      meta: { auth: true },
    },
    {
      path: '/resumes/:id/analyze',
      name: 'analyze',
      component: () => import('@/pages/AnalysisCreatePage.vue'),
      meta: { auth: true },
    },
    {
      path: '/resumes/:resumeId/analyses/:analysisId',
      name: 'analysis',
      component: () => import('@/pages/AnalysisPage.vue'),
      meta: { auth: true },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.initialized) {
    await auth.fetchUser()
  }

  if (to.meta.auth && !auth.user) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.user) {
    return { name: 'dashboard' }
  }
})

export default router
