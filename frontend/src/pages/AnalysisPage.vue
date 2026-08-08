<template>
  <div class="space-y-6">
    <PageHeader
      :title="typeLabel"
      :eyebrow="analysis?.completed_at ? formatDateTime(analysis.completed_at) : undefined"
      :back-to="{ name: 'resume', params: { id: resumeId } }"
      back-label="Back to resume"
    >
      <template #actions>
        <button
          v-if="analysis?.status === 'completed'"
          class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium text-content transition hover:bg-surface-muted disabled:opacity-50"
          :disabled="exporting"
          @click="handleExport"
        >
          <Spinner v-if="exporting" size="0.85rem" />
          {{ exporting ? 'Preparing…' : 'Export PDF' }}
        </button>
      </template>
    </PageHeader>

    <!-- Loading -->
    <div v-if="loading" class="grid gap-6 lg:grid-cols-3">
      <Skeleton height="16rem" />
      <div class="space-y-4 lg:col-span-2">
        <Skeleton height="5rem" />
        <Skeleton height="5rem" />
        <Skeleton height="5rem" />
      </div>
    </div>

    <!-- In progress -->
    <section
      v-else-if="inProgress"
      class="rounded-xl border border-border bg-surface px-6 py-16 text-center shadow-card"
    >
      <Spinner size="2rem" class="text-brand" label="Analysing" />
      <p class="mt-4 font-medium text-content">Analysing your resume</p>
      <p class="mt-1 text-sm text-content-muted">
        This usually takes under a minute. The page updates on its own.
      </p>
    </section>

    <!-- Failed -->
    <section
      v-else-if="analysis?.status === 'failed'"
      class="rounded-xl border border-critical-border bg-critical-soft p-6"
    >
      <div class="flex gap-3">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-critical" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        </svg>
        <div>
          <p class="font-medium text-content">This analysis didn't finish</p>
          <p class="mt-1 text-sm text-content-muted">
            {{ analysis.error_message ?? 'Something went wrong. Please try again.' }}
          </p>
          <p class="mt-1 text-sm text-content-muted">This didn't count against your monthly allowance.</p>
          <RouterLink
            :to="{ name: 'analyze', params: { id: resumeId } }"
            class="mt-3 inline-block rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-on-brand transition hover:bg-brand-hover"
          >
            Try again
          </RouterLink>
        </div>
      </div>
    </section>

    <EmptyState
      v-else-if="!analysis"
      tone="critical"
      title="We couldn't load this analysis"
      description="It may have been deleted, or the link may be wrong."
    />

    <!-- Result -->
    <div v-else class="grid gap-6 lg:grid-cols-3">
      <!-- Score column -->
      <aside class="space-y-4">
        <div class="rounded-xl border border-border bg-surface p-6 shadow-card">
          <ScoreGauge :score="analysis.ats_score" :label="scoreLabel" />

          <div
            v-if="analysis.ats_score === null"
            class="mt-4 rounded-lg bg-surface-muted p-3 text-center text-xs text-content-muted"
          >
            The analysis completed but didn't return a score. The feedback below is still valid.
          </div>

          <dl v-else class="mt-6 grid grid-cols-3 gap-2 border-t border-border pt-4 text-center">
            <div v-for="row in severityCounts" :key="row.label">
              <dt class="text-xs text-content-muted">{{ row.label }}</dt>
              <dd class="tabular mt-0.5 text-lg font-semibold" :class="row.class">{{ row.count }}</dd>
            </div>
          </dl>
        </div>

        <div class="rounded-xl border border-border bg-surface p-5 shadow-card">
          <h2 class="text-sm font-medium text-content-muted">About this run</h2>
          <dl class="mt-3 space-y-2.5 text-sm">
            <div class="flex justify-between gap-3">
              <dt class="text-content-muted">Type</dt>
              <dd class="font-medium text-content">{{ typeLabel }}</dd>
            </div>
            <div class="flex justify-between gap-3">
              <dt class="text-content-muted">Completed</dt>
              <dd class="font-medium text-content">{{ formatRelative(analysis.completed_at) }}</dd>
            </div>
          </dl>
        </div>
      </aside>

      <!-- Detail column -->
      <div class="space-y-4 lg:col-span-2">
        <section v-if="result?.summary" class="rounded-xl border border-border bg-surface p-5 shadow-card">
          <h2 class="font-semibold text-content">Summary</h2>
          <!-- Interpolated, never v-html: this text comes from an uploaded resume. -->
          <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-content-muted">
            {{ result.summary }}
          </p>
        </section>

        <KeywordGaps
          v-if="result"
          :matched="result.keywords_matched"
          :missing="result.keywords_missing"
        />

        <section class="rounded-xl border border-border bg-surface p-5 shadow-card">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-content">
              What to fix
              <span class="tabular ml-1 text-sm font-normal text-content-subtle">
                {{ analysis.feedback.length }}
              </span>
            </h2>

            <div v-if="analysis.feedback.length" class="flex gap-1 rounded-lg border border-border p-1">
              <button
                v-for="option in filterOptions"
                :key="option.value"
                class="rounded-md px-2.5 py-1 text-xs font-medium transition"
                :class="filter === option.value
                  ? 'bg-brand text-on-brand'
                  : 'text-content-muted hover:text-content'"
                @click="filter = option.value"
              >
                {{ option.label }}
                <span class="tabular ml-0.5 opacity-70">{{ option.count }}</span>
              </button>
            </div>
          </div>

          <EmptyState
            v-if="!analysis.feedback.length"
            title="No specific issues found"
            description="Nothing was flagged in this pass."
            class="!py-8"
          />

          <div v-else class="mt-4 space-y-2">
            <FeedbackItem v-for="item in visibleFeedback" :key="item.id" :feedback="item" />
            <p v-if="!visibleFeedback.length" class="py-6 text-center text-sm text-content-muted">
              Nothing at this severity.
            </p>
          </div>
        </section>

        <InsightList v-if="result" title="What's working" tone="success" :items="result.strengths" />
        <InsightList v-if="result" title="What's holding it back" tone="critical" :items="result.weaknesses" />
        <InsightList v-if="result" title="Gaps against the role" tone="critical" :items="result.gaps" />
        <InsightList v-if="result" title="Do these next" tone="brand" :items="result.recommendations" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAnalysisStore } from '@/stores/analysis'
import { useToastStore } from '@/stores/toast'
import { messageFor, upgradeActionFor } from '@/services/errors'
import { formatDateTime, formatRelative } from '@/services/format'
import api from '@/services/api'
import PageHeader from '@/components/ui/PageHeader.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import Skeleton from '@/components/ui/Skeleton.vue'
import Spinner from '@/components/ui/Spinner.vue'
import ScoreGauge from '@/components/charts/ScoreGauge.vue'
import FeedbackItem from '@/components/analysis/FeedbackItem.vue'
import InsightList from '@/components/analysis/InsightList.vue'
import KeywordGaps from '@/components/analysis/KeywordGaps.vue'

const route = useRoute()
const analysisStore = useAnalysisStore()
const toast = useToastStore()

const resumeId = Number(route.params.resumeId)
const analysisId = Number(route.params.analysisId)

const loading = ref(true)
const exporting = ref(false)
const filter = ref<'all' | 'critical' | 'warning' | 'info'>('all')

const analysis = computed(() => analysisStore.currentAnalysis)
const result = computed(() => analysis.value?.result ?? null)

const inProgress = computed(
  () => analysis.value?.status === 'pending' || analysis.value?.status === 'processing'
)

const TYPE_LABELS: Record<string, { title: string; score: string }> = {
  ats: { title: 'ATS check', score: 'ATS score' },
  content: { title: 'Content review', score: 'Content score' },
  formatting: { title: 'Formatting check', score: 'Format score' },
  comparison: { title: 'Job comparison', score: 'Match score' },
}

const typeLabel = computed(() => TYPE_LABELS[analysis.value?.type ?? '']?.title ?? 'Analysis')
const scoreLabel = computed(() => TYPE_LABELS[analysis.value?.type ?? '']?.score ?? 'Score')

const counts = computed(() => {
  const tally = { critical: 0, warning: 0, info: 0 }
  for (const item of analysis.value?.feedback ?? []) tally[item.severity as keyof typeof tally]++
  return tally
})

const severityCounts = computed(() => [
  { label: 'Critical', count: counts.value.critical, class: 'text-critical' },
  { label: 'Warnings', count: counts.value.warning, class: 'text-warning' },
  { label: 'Suggestions', count: counts.value.info, class: 'text-info' },
])

const filterOptions = computed(() => [
  { value: 'all' as const, label: 'All', count: analysis.value?.feedback.length ?? 0 },
  { value: 'critical' as const, label: 'Critical', count: counts.value.critical },
  { value: 'warning' as const, label: 'Warnings', count: counts.value.warning },
  { value: 'info' as const, label: 'Suggestions', count: counts.value.info },
])

const visibleFeedback = computed(() => {
  const items = analysis.value?.feedback ?? []
  if (filter.value === 'all') return items
  return items.filter((item) => item.severity === filter.value)
})

/** The job runs on a worker, so the page has to watch for it to finish. */
let pollTimer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  try {
    await analysisStore.fetchAnalysis(resumeId, analysisId)
  } catch (e) {
    toast.error(await messageFor(e))
  } finally {
    loading.value = false
  }

  if (inProgress.value) startPolling()
})

onBeforeUnmount(stopPolling)

function startPolling() {
  pollTimer = setInterval(async () => {
    try {
      const { data } = await api.get(`/resumes/${resumeId}/analyses/${analysisId}/status`)

      if (data.status === 'completed' || data.status === 'failed') {
        stopPolling()
        await analysisStore.fetchAnalysis(resumeId, analysisId)

        if (data.status === 'completed') toast.success('Your analysis is ready.')
      }
    } catch {
      stopPolling()
    }
  }, 3000)
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

async function handleExport() {
  exporting.value = true

  try {
    const response = await api.get(`/resumes/${resumeId}/analyses/${analysisId}/export`, {
      responseType: 'blob',
    })

    const url = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href = url
    link.download = `resumeai-${analysis.value?.type ?? 'analysis'}-${analysisId}.pdf`
    link.click()
    URL.revokeObjectURL(url)

    toast.success('Report downloaded.')
  } catch (e) {
    // A refused export still arrives as a Blob, so the reason has to be read
    // out of it rather than written to disk as a .pdf.
    toast.error(await messageFor(e), await upgradeActionFor(e))
  } finally {
    exporting.value = false
  }
}
</script>
