<template>
  <div class="space-y-8">
    <PageHeader
      title="Dashboard"
      :description="greeting"
    >
      <template #actions>
        <RouterLink
          to="/resumes"
          class="rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-on-brand transition hover:bg-brand-hover"
        >
          Upload a resume
        </RouterLink>
      </template>
    </PageHeader>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="rounded-xl border border-border bg-surface p-4 shadow-card sm:p-5"
      >
        <p class="text-sm font-medium text-content-muted">{{ stat.label }}</p>
        <Skeleton v-if="loading" height="2rem" width="3rem" class="mt-2" />
        <p v-else class="tabular mt-1 text-3xl font-semibold tracking-tight text-content">
          {{ stat.value }}
        </p>
        <p v-if="stat.hint && !loading" class="mt-1 text-xs text-content-subtle">{{ stat.hint }}</p>
      </div>
    </div>

    <!-- Quota warning, only when it matters -->
    <div
      v-if="quotaExhausted"
      class="flex flex-col gap-3 rounded-xl border border-warning-border bg-warning-soft p-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="flex items-start gap-3">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        </svg>
        <div>
          <p class="text-sm font-medium text-content">You've used all your analyses this month</p>
          <p class="mt-0.5 text-sm text-content-muted">Upgrade for more, or wait until your allowance resets.</p>
        </div>
      </div>
      <RouterLink
        to="/billing"
        class="shrink-0 rounded-lg bg-brand px-4 py-2 text-center text-sm font-semibold text-on-brand transition hover:bg-brand-hover"
      >
        See plans
      </RouterLink>
    </div>

    <!-- Recent resumes -->
    <section class="rounded-xl border border-border bg-surface shadow-card">
      <div class="flex items-center justify-between border-b border-border px-5 py-4">
        <h2 class="font-semibold text-content">Your resumes</h2>
        <RouterLink
          v-if="resumeStore.resumes.length"
          to="/resumes"
          class="text-sm font-medium text-brand transition hover:text-brand-hover"
        >
          View all
        </RouterLink>
      </div>

      <div v-if="loading" class="divide-y divide-border">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 px-5 py-4">
          <Skeleton width="2.5rem" height="2.5rem" />
          <div class="flex-1 space-y-2">
            <Skeleton width="40%" height="0.9rem" />
            <Skeleton width="25%" height="0.75rem" />
          </div>
        </div>
      </div>

      <EmptyState
        v-else-if="!resumeStore.resumes.length"
        title="No resumes yet"
        description="Upload a PDF or DOCX and we'll check it against applicant tracking systems."
      >
        <RouterLink
          to="/resumes"
          class="rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-on-brand transition hover:bg-brand-hover"
        >
          Upload your first resume
        </RouterLink>
      </EmptyState>

      <ul v-else class="divide-y divide-border">
        <li v-for="resume in recentResumes" :key="resume.id">
          <RouterLink
            :to="{ name: 'resume', params: { id: resume.id } }"
            class="flex items-center gap-4 px-5 py-4 transition hover:bg-surface-muted"
          >
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" />
              </svg>
            </span>
            <div class="min-w-0 flex-1">
              <p class="truncate font-medium text-content">{{ resume.title }}</p>
              <p class="mt-0.5 text-sm text-content-muted">
                {{ resume.analyses_count }} {{ resume.analyses_count === 1 ? 'analysis' : 'analyses' }}
                &middot; {{ formatDate(resume.created_at) }}
              </p>
            </div>
            <ExtractionBadge :resume="resume" />
          </RouterLink>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useResumeStore } from '@/stores/resume'
import { useBillingStore } from '@/stores/billing'
import { useToastStore } from '@/stores/toast'
import { messageFor } from '@/services/errors'
import { formatDate } from '@/services/format'
import PageHeader from '@/components/ui/PageHeader.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import Skeleton from '@/components/ui/Skeleton.vue'
import ExtractionBadge from '@/components/resume/ExtractionBadge.vue'

const route = useRoute()
const auth = useAuthStore()
const resumeStore = useResumeStore()
const billingStore = useBillingStore()
const toast = useToastStore()

const loading = ref(true)

const greeting = computed(() => {
  const first = auth.user?.name?.split(' ')[0]
  return first ? `Welcome back, ${first}.` : 'Your resumes and analyses at a glance.'
})

const recentResumes = computed(() => resumeStore.resumes.slice(0, 5))

const analysesLeft = computed(() => {
  const usage = billingStore.usage
  if (!usage) return '—'
  if (usage.analysis_limit === -1) return 'Unlimited'
  return String(Math.max(0, usage.analysis_limit - usage.analyses_used))
})

const quotaExhausted = computed(() => {
  const usage = billingStore.usage
  return !!usage && usage.analysis_limit !== -1 && usage.analyses_used >= usage.analysis_limit
})

const stats = computed(() => [
  {
    label: 'Resumes',
    value: String(resumeStore.total ?? resumeStore.resumes.length),
  },
  {
    label: 'Analyses run',
    value: String(billingStore.usage?.analyses_used ?? 0),
    hint: 'This month',
  },
  {
    label: 'Plan',
    value: capitalise(billingStore.subscription?.plan ?? 'free'),
  },
  {
    label: 'Analyses left',
    value: analysesLeft.value,
    hint: billingStore.usage?.analysis_limit === -1 ? undefined : 'Resets monthly',
  },
])

function capitalise(value: string) {
  return value.charAt(0).toUpperCase() + value.slice(1)
}

onMounted(async () => {
  if (route.query.upgraded) {
    toast.success('Your plan is now active. Enjoy the new features.')
  } else if (route.query.cancelled) {
    toast.info('Checkout cancelled. Your plan is unchanged.')
  }

  try {
    await Promise.all([
      resumeStore.fetchResumes(),
      billingStore.fetchSubscription(),
      billingStore.fetchUsage(),
    ])
  } catch (e) {
    toast.error(await messageFor(e))
  } finally {
    loading.value = false
  }
})
</script>
