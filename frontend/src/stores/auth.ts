import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { User } from '@/types'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const initialized = ref(false)

  async function fetchUser() {
    try {
      loading.value = true
      const { data } = await api.get('/auth/user')
      user.value = data
    } catch {
      user.value = null
    } finally {
      loading.value = false
      initialized.value = true
    }
  }

  function loginWithGoogle() {
    window.location.href = `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/auth/google`
  }

  async function logout() {
    await api.post('/auth/logout')
    user.value = null
    window.location.href = '/'
  }

  return { user, loading, initialized, fetchUser, loginWithGoogle, logout }
})
