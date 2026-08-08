import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

interface Plan {
  id: string
  name: string
  price: number
  price_display: string
  features: string[]
  /** False when this plan cannot currently be bought (billing not configured). */
  purchasable: boolean
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

export const useBillingStore = defineStore('billing', () => {
  const plans = ref<Plan[]>([])
  const subscription = ref<Subscription | null>(null)
  const usage = ref<Usage | null>(null)
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

  return { plans, subscription, usage, loading, fetchPlans, fetchSubscription, fetchUsage, checkout, openPortal, fetchAll }
})
