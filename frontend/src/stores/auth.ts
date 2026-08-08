import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { User } from '@/types'
import api from '@/services/api'

const INTENDED_ROUTE_KEY = 'resumeai:intended-route'
const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const initialized = ref(false)

  async function fetchUser() {
    // The router guard and any page may both ask; only the first call matters.
    if (loading.value) return

    try {
      loading.value = true
      const { data } = await api.get('/auth/user')
      user.value = data.data
    } catch {
      user.value = null
    } finally {
      loading.value = false
      initialized.value = true
    }
  }

  function loginWithGoogle() {
    window.location.href = `${API_BASE}/auth/google`
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      user.value = null
      sessionStorage.removeItem(INTENDED_ROUTE_KEY)
      window.location.href = '/'
    }
  }

  /** Survives the full-page OAuth redirect, unlike in-memory state. */
  function rememberIntendedRoute(path: string) {
    sessionStorage.setItem(INTENDED_ROUTE_KEY, path)
  }

  function takeIntendedRoute(): string | null {
    const path = sessionStorage.getItem(INTENDED_ROUTE_KEY)
    sessionStorage.removeItem(INTENDED_ROUTE_KEY)
    return path
  }

  return {
    user,
    loading,
    initialized,
    fetchUser,
    loginWithGoogle,
    logout,
    rememberIntendedRoute,
    takeIntendedRoute,
  }
})
