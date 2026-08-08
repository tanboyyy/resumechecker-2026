<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Billing</h1>
      <p class="text-gray-600 mt-1">Manage your subscription and usage</p>
    </div>

    <!-- Current Plan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Plan</h2>
      <div v-if="billing.subscription" class="flex items-center justify-between">
        <div>
          <span class="text-2xl font-bold text-gray-900 capitalize">{{ billing.subscription.plan }}</span>
          <span class="ml-3 px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
            {{ billing.subscription.status }}
          </span>
        </div>
        <button
          v-if="billing.subscription.plan !== 'free'"
          @click="manageSubscription"
          :disabled="openingPortal"
          class="text-sm text-indigo-600 hover:text-indigo-700 font-medium disabled:opacity-50"
        >
          {{ openingPortal ? 'Opening…' : 'Manage subscription' }}
        </button>
      </div>
      <div v-else>
        <span class="text-2xl font-bold text-gray-900">Free</span>
      </div>
    </div>

    <!-- Usage -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Usage This Month</h2>
      <div v-if="billing.usage" class="grid grid-cols-3 gap-6">
        <div>
          <div class="text-sm text-gray-500">Analyses Used</div>
          <div class="text-2xl font-bold text-gray-900">
            {{ billing.usage.analyses_used }}
            <span class="text-sm font-normal text-gray-500">/ {{ billing.usage.analysis_limit === -1 ? '∞' : billing.usage.analysis_limit }}</span>
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Resumes</div>
          <div class="text-2xl font-bold text-gray-900">{{ billing.usage.resumes_count }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Plan</div>
          <div class="text-2xl font-bold text-gray-900 capitalize">{{ billing.subscription?.plan ?? 'Free' }}</div>
        </div>
      </div>
    </div>

    <!-- Plans -->
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Upgrade Plan</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="plan in billing.plans"
          :key="plan.id"
          :class="[
            'rounded-xl border-2 p-6 transition',
            billing.subscription?.plan === plan.id
              ? 'border-indigo-500 bg-indigo-50'
              : 'border-gray-200 bg-white hover:border-gray-300',
          ]"
        >
          <h3 class="text-lg font-semibold text-gray-900">{{ plan.name }}</h3>
          <div class="text-3xl font-bold text-gray-900 mt-2">{{ plan.price_display }}</div>
          <ul class="mt-4 space-y-2">
            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ feature }}
            </li>
          </ul>
          <button
            v-if="plan.id !== 'free' && billing.subscription?.plan !== plan.id"
            @click="handleUpgrade(plan.id)"
            :disabled="pendingPlan !== null"
            class="w-full mt-6 px-4 py-2 rounded-lg font-medium transition bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50"
          >
            {{
              pendingPlan === plan.id
                ? 'Starting checkout…'
                : billing.subscription?.plan === 'free' ? 'Upgrade' : 'Switch'
            }}
          </button>
          <div v-else-if="billing.subscription?.plan === plan.id" class="mt-6 text-center text-sm font-medium text-indigo-600">
            Current Plan
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useBillingStore } from '@/stores/billing'
import { useToastStore } from '@/stores/toast'
import { messageFor } from '@/services/errors'

const billing = useBillingStore()
const toast = useToastStore()

/** Which plan's button is mid-request, so only that one shows a spinner. */
const pendingPlan = ref<string | null>(null)
const openingPortal = ref(false)

onMounted(async () => {
  try {
    await billing.fetchAll()
  } catch (e) {
    toast.error(await messageFor(e))
  }
})

async function handleUpgrade(planId: string) {
  pendingPlan.value = planId

  try {
    const url = await billing.checkout(planId)

    if (!url) throw new Error('We could not start checkout. Please try again.')

    window.location.href = url
  } catch (e) {
    toast.error(await messageFor(e))
    pendingPlan.value = null
  }
}

async function manageSubscription() {
  openingPortal.value = true

  try {
    const url = await billing.openPortal()

    if (!url) throw new Error('We could not open the billing portal. Please try again.')

    window.location.href = url
  } catch (e) {
    toast.error(await messageFor(e))
    openingPortal.value = false
  }
}
</script>
