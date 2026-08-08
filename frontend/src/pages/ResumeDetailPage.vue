<template>
  <div class="space-y-6">
    <PageHeader
      :title="resume?.title ?? 'Resume'"
      back-to="/resumes"
      back-label="Back to resumes"
      :description="resume ? `${resume.original_filename} · ${resume.size_human}` : undefined"
    >
      <template #actions>
        <Button variant="secondary" @click="handleDownload">Download</Button>
        <Button
          v-if="resume?.extraction_status === 'completed'"
          tag="router-link"
          :to="{ name: 'analyze', params: { id: resumeId } }"
        >
          Run analysis
        </Button>
      </template>
    </PageHeader>

    <!-- Loading -->
    <div v-if="loading" class="grid gap-6 lg:grid-cols-3">
      <div class="space-y-4 lg:col-span-2">
        <Skeleton height="24rem" />
      </div>
      <Skeleton height="12rem" />
    </div>

    <EmptyState
      v-else-if="!resume"
      tone="critical"
      title="We couldn't load this resume"
      description="It may have been deleted, or the link may be wrong."
    >
      <Button tag="router-link" to="/resumes">Back to resumes</Button>
    </EmptyState>

    <template v-else>
      <!-- Extraction failed: the single most important thing to say -->
      <div
        v-if="resume.extraction_status === 'failed'"
        class="flex gap-3 rounded-xl border border-critical-border bg-critical-soft p-4"
      >
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-critical" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        </svg>
        <div>
          <p class="font-medium text-content">We couldn't read this file</p>
          <p class="mt-1 text-sm text-content-muted">
            {{ resume.extraction_error ?? 'Try uploading a text-based PDF or DOCX.' }}
          </p>
          <RouterLink to="/resumes" class="mt-2 inline-block text-sm font-semibold text-critical underline underline-offset-2">
            Upload a different file
          </RouterLink>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
          <!-- Preview -->
          <section class="overflow-hidden rounded-xl border border-border bg-surface shadow-card">
            <div class="flex items-center justify-between border-b border-border px-5 py-3">
              <h2 class="font-semibold text-content">Preview</h2>
              <button class="text-sm font-medium text-brand transition hover:text-brand-hover" @click="handleDownload">
                Open in new tab
              </button>
            </div>

            <div v-if="isPdf" class="h-[32rem] bg-surface-muted sm:h-[42rem]">
              <iframe v-if="previewUrl" :src="previewUrl" class="h-full w-full border-0" title="Resume preview" />
              <div v-else class="grid h-full place-items-center">
                <Spinner size="1.5rem" class="text-content-subtle" label="Loading preview" />
              </div>
            </div>
            <EmptyState
              v-else
              title="No preview for this format"
              description="Download the file to view it."
            />
          </section>

          <!-- Extracted text -->
          <section class="overflow-hidden rounded-xl border border-border bg-surface shadow-card">
            <button
              class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-surface-muted"
              :aria-expanded="showText"
              @click="showText = !showText"
            >
              <span class="flex items-center gap-3">
                <span class="font-semibold text-content">Extracted text</span>
                <ExtractionBadge :resume="resume" />
              </span>
              <svg
                class="h-5 w-5 shrink-0 text-content-subtle transition-transform"
                :class="showText && 'rotate-180'"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
              </svg>
            </button>

            <div v-show="showText" class="border-t border-border px-5 py-4">
              <pre
                v-if="resume.extracted_text"
                class="max-h-96 overflow-auto whitespace-pre-wrap rounded-lg bg-surface-muted p-4 font-mono text-xs leading-relaxed text-content-muted"
              >{{ resume.extracted_text }}</pre>
              <p v-else-if="resume.extraction_status === 'pending'" class="py-6 text-center text-sm text-content-muted">
                Reading the text from your file…
              </p>
              <p v-else class="py-6 text-center text-sm text-content-muted">
                No text was extracted from this file.
              </p>
            </div>
          </section>

          <!-- Analyses -->
          <section class="overflow-hidden rounded-xl border border-border bg-surface shadow-card">
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
              <h2 class="font-semibold text-content">Analyses</h2>
              <RouterLink
                v-if="resume.extraction_status === 'completed'"
                :to="{ name: 'analyze', params: { id: resume.id } }"
                class="text-sm font-medium text-brand transition hover:text-brand-hover"
              >
                Run new
              </RouterLink>
            </div>

            <EmptyState
              v-if="!analyses.length"
              title="No analyses yet"
              :description="resume.extraction_status === 'completed'
                ? 'Run one to see how this resume scores.'
                : 'Once we finish reading the file you can run an analysis.'"
            />

            <ul v-else class="divide-y divide-border">
              <li v-for="analysis in analyses" :key="analysis.id">
                <RouterLink
                  :to="{ name: 'analysis', params: { resumeId: resume.id, analysisId: analysis.id } }"
                  class="flex items-center gap-4 px-5 py-4 transition hover:bg-surface-muted"
                >
                  <ScoreChip :analysis="analysis" />
                  <div class="min-w-0 flex-1">
                    <p class="font-medium capitalize text-content">{{ typeLabel(analysis.type) }}</p>
                    <p class="mt-0.5 text-sm text-content-muted">{{ formatRelative(analysis.created_at) }}</p>
                  </div>
                  <StatusPill :status="analysis.status" />
                </RouterLink>
              </li>
            </ul>

            <Pagination
              v-if="analysisStore.lastPage > 1"
              :page="analysisStore.page"
              :last-page="analysisStore.lastPage"
              :total="analysisStore.total"
              label="analyses"
              @change="loadAnalyses"
            />
          </section>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-4">
          <section class="rounded-xl border border-border bg-surface p-5 shadow-card">
            <h2 class="text-sm font-medium text-content-muted">Details</h2>
            <dl class="mt-3 space-y-3 text-sm">
              <div class="flex items-center justify-between gap-3">
                <dt class="text-content-muted">Status</dt>
                <dd><ExtractionBadge :resume="resume" /></dd>
              </div>
              <div class="flex items-center justify-between gap-3">
                <dt class="text-content-muted">Analyses</dt>
                <dd class="tabular font-medium text-content">{{ resume.analyses_count }}</dd>
              </div>
              <div class="flex items-center justify-between gap-3">
                <dt class="text-content-muted">Size</dt>
                <dd class="font-medium text-content">{{ resume.size_human }}</dd>
              </div>
              <div class="flex items-center justify-between gap-3">
                <dt class="text-content-muted">Uploaded</dt>
                <dd class="font-medium text-content">{{ formatDate(resume.created_at) }}</dd>
              </div>
            </dl>
          </section>

          <Button variant="destructive" class="w-full" @click="handleDelete">Delete resume</Button>
        </aside>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useResumeStore } from '@/stores/resume'
import { useAnalysisStore } from '@/stores/analysis'
import { useToastStore } from '@/stores/toast'
import { confirm } from '@/composables/useConfirm'
import { messageFor } from '@/services/errors'
import { formatDate, formatRelative } from '@/services/format'
import api from '@/services/api'
import type { Resume } from '@/types'
import PageHeader from '@/components/ui/PageHeader.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import Skeleton from '@/components/ui/Skeleton.vue'
import Spinner from '@/components/ui/Spinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import Button from '@/components/ui/Button.vue'
import ExtractionBadge from '@/components/resume/ExtractionBadge.vue'
import ScoreChip from '@/components/analysis/ScoreChip.vue'
import StatusPill from '@/components/analysis/StatusPill.vue'

const route = useRoute()
const router = useRouter()
const resumeStore = useResumeStore()
const analysisStore = useAnalysisStore()
const toast = useToastStore()

const resumeId = Number(route.params.id)
const resume = ref<Resume | null>(null)
const loading = ref(true)
const showText = ref(false)

const analyses = computed(() => analysisStore.analyses)
const isPdf = computed(() => !!resume.value?.mime_type?.includes('pdf'))

/**
 * The iframe lives on a different origin from the API, so the session cookie
 * is not sent with its request. A short-lived signed URL carries the
 * authorisation instead.
 */
const previewUrl = ref('')

async function loadPreviewUrl() {
  try {
    const { data } = await api.get(`/resumes/${resumeId}/preview-url`)
    previewUrl.value = data.url
  } catch {
    previewUrl.value = ''
  }
}

let pollTimer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  try {
    resume.value = await resumeStore.getResume(resumeId)
    await loadAnalyses()
    startPollingIfNeeded()

    if (isPdf.value) loadPreviewUrl()
  } catch (e) {
    if (!resume.value) toast.error(await messageFor(e))
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(stopPolling)

async function loadAnalyses(page = 1) {
  try {
    await analysisStore.fetchAnalyses(resumeId, page)
  } catch (e) {
    toast.error(await messageFor(e))
  }
}

/** Extraction runs on a worker, so the page has to find out when it finishes. */
function startPollingIfNeeded() {
  if (resume.value?.extraction_status !== 'pending' || pollTimer) return

  pollTimer = setInterval(async () => {
    const updated = await resumeStore.refreshResume(resumeId)
    if (!updated) return stopPolling()

    resume.value = updated

    if (updated.extraction_status !== 'pending') {
      stopPolling()
      if (updated.extraction_status === 'completed') {
        toast.success('Your resume is ready to analyse.')
      }
    }
  }, 3000)
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

function typeLabel(type: string) {
  return { ats: 'ATS check', content: 'Content review', formatting: 'Formatting check', comparison: 'Job comparison' }[type] ?? type
}

function handleDownload() {
  const base = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  window.open(`${base}/resumes/${resumeId}/download`, '_blank', 'noopener')
}

async function handleDelete() {
  if (!resume.value) return

  const confirmed = await confirm({
    title: `Delete "${resume.value.title}"?`,
    description: 'This also deletes its analyses. This cannot be undone.',
    confirmLabel: 'Delete',
  })

  if (!confirmed) return

  try {
    await resumeStore.deleteResume(resumeId)
    toast.success('Resume deleted.')
    router.push('/resumes')
  } catch (e) {
    toast.error(await messageFor(e))
  }
}
</script>
