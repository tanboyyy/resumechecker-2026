import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

interface Plan {
  id: string
  name: string
  price: number
  price_display: string
  features: string[]
}

interface Subscription {
  plan: string
  status: string
  ends_at: string | null
  limits: Record<string, unknown>
}

interface Usage {
  analyses_used: number
  analysis_limit: number
  resumes_count: number
}

interface GeminiStatus {
  connected: boolean
  model?: string
  rate_limit?: number | null
  rate_remaining?: number | null
  rate_reset?: string | null
  error?: string
}

export const useBillingStore = defineStore('billing', () => {
  const plans = ref<Plan[]>([])
  const subscription = ref<Subscription | null>(null)
  const usage = ref<Usage | null>(null)
  const geminiStatus = ref<GeminiStatus | null>(null)
  const loading = ref(false)

  async function fetchPlans() {
    const { data } = await api.get('/billing/plans')
    plans.value = data.plans
  }

  async function fetchSubscription() {
    const { data } = await api.get('/billing/subscription')
    subscription.value = data
  }

  async function fetchUsage() {
    const { data } = await api.get('/billing/usage')
    usage.value = data
  }

  async function fetchGeminiStatus() {
    try {
      const { data } = await api.get('/gemini/status')
      geminiStatus.value = data
    } catch {
      geminiStatus.value = { connected: false, error: 'Failed to check status' }
    }
  }

  async function checkout(plan: string): Promise<string> {
    const { data } = await api.post('/billing/checkout', { plan })
    return data.url
  }

  async function openPortal(): Promise<string> {
    const { data } = await api.post('/billing/portal')
    return data.url
  }

  async function fetchAll() {
    loading.value = true
    try {
      await Promise.all([fetchPlans(), fetchSubscription(), fetchUsage()])
    } finally {
      loading.value = false
    }
  }

  return { plans, subscription, usage, geminiStatus, loading, fetchPlans, fetchSubscription, fetchUsage, fetchGeminiStatus, checkout, openPortal, fetchAll }
})
