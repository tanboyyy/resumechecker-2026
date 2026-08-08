<template>
  <div class="space-y-6">
    <PageHeader
      title="Resumes"
      description="Upload a resume, then run an analysis against it."
    />

    <!-- Upload -->
    <div
      class="rounded-xl border-2 border-dashed p-8 text-center transition"
      :class="[
        dragging ? 'border-brand bg-brand-soft' : 'border-border-strong hover:border-brand',
        resumeStore.uploading && 'pointer-events-none opacity-60',
      ]"
      @dragover.prevent="dragging = true"
      @dragleave="dragging = false"
      @drop.prevent="handleDrop"
    >
      <div v-if="resumeStore.uploading" class="flex flex-col items-center gap-3">
        <Spinner size="2rem" class="text-brand" label="Uploading" />
        <p class="text-sm text-content-muted">Uploading {{ uploadingName }}…</p>
      </div>

      <div v-else class="flex flex-col items-center gap-2">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-surface-muted text-content-subtle" aria-hidden="true">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
          </svg>
        </span>
        <p class="text-content">
          Drag a file here, or
          <label class="cursor-pointer font-semibold text-brand transition hover:text-brand-hover">
            <span>browse</span>
            <input type="file" accept=".pdf,.docx" class="sr-only" @change="handleFileSelect" />
          </label>
        </p>
        <p class="text-sm text-content-subtle">PDF or DOCX, up to 20&nbsp;MB</p>
      </div>
    </div>

    <!-- List -->
    <section class="overflow-hidden rounded-xl border border-border bg-surface shadow-card">
      <div v-if="resumeStore.loading" class="divide-y divide-border">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 p-4">
          <Skeleton width="2.5rem" height="2.5rem" />
          <div class="flex-1 space-y-2">
            <Skeleton width="35%" height="0.9rem" />
            <Skeleton width="20%" height="0.75rem" />
          </div>
        </div>
      </div>

      <EmptyState
        v-else-if="!resumeStore.resumes.length"
        title="No resumes yet"
        description="Upload one above and we'll pull the text out automatically."
      />

      <ul v-else class="divide-y divide-border">
        <li
          v-for="resume in resumeStore.resumes"
          :key="resume.id"
          class="group relative flex items-center gap-4 p-4 transition hover:bg-surface-muted"
        >
          <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" />
            </svg>
          </span>

          <div class="min-w-0 flex-1">
            <RouterLink
              :to="{ name: 'resume', params: { id: resume.id } }"
              class="font-medium text-content after:absolute after:inset-0 after:content-['']"
            >
              <span class="block truncate">{{ resume.title }}</span>
            </RouterLink>
            <p class="mt-0.5 truncate text-sm text-content-muted">
              {{ resume.size_human }} &middot; {{ resume.analyses_count }}
              {{ resume.analyses_count === 1 ? 'analysis' : 'analyses' }} &middot;
              {{ formatDate(resume.created_at) }}
            </p>
          </div>

          <ExtractionBadge :resume="resume" class="hidden sm:inline-flex" />

          <button
            class="relative z-10 rounded-lg p-2 text-content-subtle transition hover:bg-critical-soft hover:text-critical"
            :aria-label="`Delete ${resume.title}`"
            @click="handleDelete(resume)"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
            </svg>
          </button>
        </li>
      </ul>

      <Pagination
        v-if="resumeStore.lastPage > 1"
        :page="resumeStore.page"
        :last-page="resumeStore.lastPage"
        :total="resumeStore.total"
        label="resumes"
        @change="resumeStore.fetchResumes($event)"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink } from 'vue-router'
import { useResumeStore } from '@/stores/resume'
import { useToastStore } from '@/stores/toast'
import { confirm } from '@/composables/useConfirm'
import { messageFor, upgradeActionFor } from '@/services/errors'
import { formatDate } from '@/services/format'
import type { Resume } from '@/types'
import PageHeader from '@/components/ui/PageHeader.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import Skeleton from '@/components/ui/Skeleton.vue'
import Spinner from '@/components/ui/Spinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ExtractionBadge from '@/components/resume/ExtractionBadge.vue'

const resumeStore = useResumeStore()
const toast = useToastStore()

const dragging = ref(false)
const uploadingName = ref('')

/** Resumes still being read, polled until they settle. */
let pollTimer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  try {
    await resumeStore.fetchResumes()
    startPollingIfNeeded()
  } catch (e) {
    toast.error(await messageFor(e))
  }
})

onBeforeUnmount(stopPolling)

function startPollingIfNeeded() {
  if (pollTimer) return

  pollTimer = setInterval(async () => {
    const pending = resumeStore.resumes.filter((r) => r.extraction_status === 'pending')

    if (!pending.length) return stopPolling()

    await Promise.all(pending.map((r) => resumeStore.refreshResume(r.id)))
  }, 3000)
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (file) doUpload(file)
  input.value = ''
}

function handleDrop(event: DragEvent) {
  dragging.value = false
  const file = event.dataTransfer?.files[0]
  if (file) doUpload(file)
}

async function doUpload(file: File) {
  uploadingName.value = file.name

  try {
    const resume = await resumeStore.uploadResume(file)
    toast.success(`${resume.title} uploaded. Reading the text now…`)
    startPollingIfNeeded()
  } catch (e) {
    toast.error(await messageFor(e), await upgradeActionFor(e))
  } finally {
    uploadingName.value = ''
  }
}

async function handleDelete(resume: Resume) {
  const confirmed = await confirm({
    title: `Delete "${resume.title}"?`,
    description: 'This also deletes its analyses. This cannot be undone.',
    confirmLabel: 'Delete',
  })

  if (!confirmed) return

  try {
    await resumeStore.deleteResume(resume.id)
    toast.success('Resume deleted.')
  } catch (e) {
    toast.error(await messageFor(e))
  }
}
</script>
