<template>
  <div class="space-y-6">
    <PageHeader title="Billing" description="Your plan, usage, and payment details." />

    <!-- Current plan + usage -->
    <section class="grid gap-4 sm:grid-cols-3">
      <div class="rounded-xl border border-border bg-surface p-5 shadow-card sm:col-span-1">
        <p class="text-sm font-medium text-content-muted">Current plan</p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
          <span class="text-2xl font-semibold capitalize tracking-tight text-content">
            {{ billing.subscription?.plan ?? 'Free' }}
          </span>
          <span
            v-if="billing.subscription?.status"
            class="rounded-full border border-success-border bg-success-soft px-2 py-0.5 text-xs font-medium capitalize text-success"
          >
            {{ billing.subscription.status }}
          </span>
        </div>
        <button
          v-if="billing.subscription && billing.subscription.plan !== 'free'"
          class="mt-4 text-sm font-medium text-brand transition hover:text-brand-hover disabled:opacity-50"
          :disabled="openingPortal"
          @click="manageSubscription"
        >
          {{ openingPortal ? 'Opening…' : 'Manage subscription' }}
        </button>
      </div>

      <div class="rounded-xl border border-border bg-surface p-5 shadow-card sm:col-span-2">
        <p class="text-sm font-medium text-content-muted">Analyses this month</p>

        <template v-if="billing.usage">
          <p class="tabular mt-2 text-2xl font-semibold tracking-tight text-content">
            {{ billing.usage.analyses_used }}
            <span class="text-base font-normal text-content-muted">
              / {{ billing.usage.analysis_limit === -1 ? 'unlimited' : billing.usage.analysis_limit }}
            </span>
          </p>

          <div
            v-if="billing.usage.analysis_limit !== -1"
            class="mt-3 h-2 overflow-hidden rounded-full bg-surface-muted"
            role="progressbar"
            :aria-valuenow="billing.usage.analyses_used"
            :aria-valuemin="0"
            :aria-valuemax="billing.usage.analysis_limit"
          >
            <div
              class="h-full rounded-full transition-all"
              :class="usageRatio >= 1 ? 'bg-critical' : usageRatio >= 0.8 ? 'bg-warning' : 'bg-brand'"
              :style="{ width: `${Math.min(100, usageRatio * 100)}%` }"
            />
          </div>

          <p class="mt-2 text-sm text-content-muted">
            {{ billing.usage.resumes_count }}
            {{ billing.usage.resumes_count === 1 ? 'resume' : 'resumes' }} stored
          </p>
        </template>

        <Skeleton v-else height="2rem" width="6rem" class="mt-2" />
      </div>
    </section>

    <!-- Plans -->
    <section>
      <h2 class="font-semibold text-content">Plans</h2>

      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div
          v-for="plan in billing.plans"
          :key="plan.id"
          class="flex flex-col rounded-xl border-2 bg-surface p-5 transition"
          :class="isCurrent(plan.id) ? 'border-brand' : 'border-border hover:border-border-strong'"
        >
          <div class="flex items-baseline justify-between gap-2">
            <h3 class="font-semibold text-content">{{ plan.name }}</h3>
            <span
              v-if="isCurrent(plan.id)"
              class="rounded-full bg-brand-soft px-2 py-0.5 text-xs font-semibold text-brand"
            >
              Current
            </span>
          </div>

          <p class="mt-2 text-2xl font-semibold tracking-tight text-content">{{ plan.price_display }}</p>

          <ul class="mt-4 flex-1 space-y-2">
            <li v-for="feature in plan.features" :key="feature" class="flex gap-2 text-sm text-content-muted">
              <svg class="mt-0.5 h-4 w-4 shrink-0 text-success" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
              </svg>
              {{ feature }}
            </li>
          </ul>

          <Button
            v-if="plan.purchasable && !isCurrent(plan.id)"
            class="mt-5 w-full"
            :disabled="pendingPlan !== null"
            :loading="pendingPlan === plan.id"
            @click="handleUpgrade(plan.id)"
          >
            {{ pendingPlan === plan.id ? 'Starting checkout…' : upgradeLabel }}
          </Button>
          <p
            v-else-if="plan.id !== 'free' && !isCurrent(plan.id)"
            class="mt-5 rounded-lg bg-surface-muted px-4 py-2.5 text-center text-sm text-content-muted"
          >
            Not available yet
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useBillingStore } from '@/stores/billing'
import { useToastStore } from '@/stores/toast'
import { messageFor } from '@/services/errors'
import PageHeader from '@/components/ui/PageHeader.vue'
import Skeleton from '@/components/ui/Skeleton.vue'
import Button from '@/components/ui/Button.vue'

const billing = useBillingStore()
const toast = useToastStore()

const pendingPlan = ref<string | null>(null)
const openingPortal = ref(false)

const usageRatio = computed(() => {
  const usage = billing.usage
  if (!usage || usage.analysis_limit <= 0) return 0
  return usage.analyses_used / usage.analysis_limit
})

const upgradeLabel = computed(() =>
  (billing.subscription?.plan ?? 'free') === 'free' ? 'Upgrade' : 'Switch plan'
)

function isCurrent(planId: string) {
  return (billing.subscription?.plan ?? 'free') === planId
}

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
