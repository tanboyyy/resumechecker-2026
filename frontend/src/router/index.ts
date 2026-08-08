import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: (_to, _from, saved) => saved ?? { top: 0 },
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
      meta: { guest: true, title: 'Sign in' },
    },
    {
      // Every signed-in screen shares one chrome: header, navigation, account
      // menu, page width. Pages render only their own content.
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { auth: true },
      children: [
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('@/pages/DashboardPage.vue'),
          meta: { title: 'Dashboard' },
        },
        {
          path: 'resumes',
          name: 'resumes',
          component: () => import('@/pages/ResumesPage.vue'),
          meta: { title: 'Resumes' },
        },
        {
          path: 'resumes/:id',
          name: 'resume',
          component: () => import('@/pages/ResumeDetailPage.vue'),
          meta: { title: 'Resume' },
        },
        {
          path: 'resumes/:id/analyze',
          name: 'analyze',
          component: () => import('@/pages/AnalysisCreatePage.vue'),
          meta: { title: 'Run analysis' },
        },
        {
          path: 'resumes/:resumeId/analyses/:analysisId',
          name: 'analysis',
          component: () => import('@/pages/AnalysisPage.vue'),
          meta: { title: 'Analysis' },
        },
        {
          path: 'billing',
          name: 'billing',
          component: () => import('@/pages/BillingPage.vue'),
          meta: { title: 'Billing' },
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('@/pages/SettingsPage.vue'),
          meta: { title: 'Settings' },
        },
        {
          path: ':pathMatch(.*)*',
          name: 'not-found',
          component: () => import('@/pages/NotFoundPage.vue'),
          meta: { title: 'Page not found' },
        },
      ],
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.initialized) {
    await auth.fetchUser()
  }

  if (to.meta.auth && !auth.user) {
    // Remember where they were headed; consumed after the OAuth round trip.
    auth.rememberIntendedRoute(to.fullPath)
    return { name: 'login' }
  }

  if (to.meta.guest && auth.user) {
    return { name: 'dashboard' }
  }

  // Sign-in always returns to /dashboard, so honour the original destination here.
  if (auth.user && to.name === 'dashboard') {
    const intended = auth.takeIntendedRoute()
    if (intended && intended !== to.fullPath) return intended
  }
})

router.afterEach((to) => {
  const title = to.meta.title as string | undefined
  document.title = title ? `${title} · ResumeAI` : 'ResumeAI'
})

export default router
