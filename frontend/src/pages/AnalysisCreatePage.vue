<template>
  <div class="mx-auto max-w-3xl space-y-6">
    <PageHeader
      title="Run an analysis"
      description="Pick what you want checked."
      :back-to="{ name: 'resume', params: { id: resumeId } }"
      back-label="Back to resume"
    />

    <fieldset class="grid gap-3 sm:grid-cols-2">
      <legend class="sr-only">Analysis type</legend>

      <label
        v-for="type in analysisTypes"
        :key="type.value"
        class="relative flex cursor-pointer flex-col gap-2 rounded-xl border-2 bg-surface p-5 transition"
        :class="[
          selectedType === type.value ? 'border-brand bg-brand-soft' : 'border-border hover:border-border-strong',
          !isAllowed(type.value) && 'opacity-60',
        ]"
      >
        <input
          v-model="selectedType"
          type="radio"
          name="analysis-type"
          :value="type.value"
          class="sr-only"
        />

        <div class="flex items-start justify-between gap-2">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg"
                :class="selectedType === type.value ? 'bg-brand text-on-brand' : 'bg-surface-muted text-content-subtle'">
            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :d="type.icon" />
            </svg>
          </span>
          <span
            v-if="!isAllowed(type.value)"
            class="rounded-full bg-surface-muted px-2 py-0.5 text-xs font-semibold text-content-muted"
          >
            Pro
          </span>
        </div>

        <span class="font-semibold text-content">{{ type.label }}</span>
        <span class="text-sm text-content-muted">{{ type.description }}</span>
      </label>
    </fieldset>

    <!-- Job description, only when it is required -->
    <div v-if="selectedType === 'comparison'" class="rounded-xl border border-border bg-surface p-5 shadow-card">
      <label for="job-description" class="block font-medium text-content">Job description</label>
      <p class="mt-1 text-sm text-content-muted">
        Paste the posting you're applying to. We'll compare your resume against it.
      </p>
      <textarea
        id="job-description"
        v-model="jobDescription"
        rows="8"
        class="mt-3 w-full rounded-lg border border-border bg-canvas px-3 py-2 text-sm text-content placeholder:text-content-subtle focus:border-brand"
        placeholder="Paste the full job description here…"
      />
      <p class="mt-2 text-xs" :class="jobDescriptionValid ? 'text-content-subtle' : 'text-warning'">
        {{ jobDescription.length }} / 10,000 characters
        <span v-if="!jobDescriptionValid"> · at least 50 needed</span>
      </p>
    </div>

    <div v-if="error" class="rounded-xl border border-critical-border bg-critical-soft p-4">
      <p class="text-sm text-content">{{ error }}</p>
      <RouterLink
        v-if="upgrade"
        :to="upgrade.to"
        class="mt-2 inline-block text-sm font-semibold text-critical underline underline-offset-2"
      >
        {{ upgrade.label }}
      </RouterLink>
    </div>

    <div class="flex justify-end gap-3">
      <RouterLink
        :to="{ name: 'resume', params: { id: resumeId } }"
        class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-content transition hover:bg-surface-muted"
      >
        Cancel
      </RouterLink>
      <button
        class="inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-on-brand transition hover:bg-brand-hover disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="!canSubmit"
        @click="handleAnalyze"
      >
        <Spinner v-if="analysisStore.creating" size="0.9rem" />
        {{ analysisStore.creating ? 'Starting…' : 'Run analysis' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAnalysisStore } from '@/stores/analysis'
import { useBillingStore } from '@/stores/billing'
import { messageFor, upgradeActionFor } from '@/services/errors'
import type { Toast } from '@/stores/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import Spinner from '@/components/ui/Spinner.vue'

const route = useRoute()
const router = useRouter()
const analysisStore = useAnalysisStore()
const billing = useBillingStore()

const resumeId = Number(route.params.id)
const selectedType = ref<string>('ats')
const jobDescription = ref('')
const error = ref('')
const upgrade = ref<Toast['action'] | null>(null)

const analysisTypes = [
  {
    value: 'ats',
    label: 'ATS check',
    description: 'How well applicant tracking systems can parse and rank your resume.',
    icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2zM9 9h6v6H9V9z',
  },
  {
    value: 'content',
    label: 'Content review',
    description: 'Whether your bullet points show impact, with concrete rewrites.',
    icon: 'M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
  },
  {
    value: 'formatting',
    label: 'Formatting check',
    description: 'Structure, spacing, headings, and date consistency.',
    icon: 'M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5zM4 13a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6zM16 13a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-6z',
  },
  {
    value: 'comparison',
    label: 'Job comparison',
    description: 'Match your resume against a specific posting and find the gaps.',
    icon: 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z',
  },
]

const allowedTypes = computed(
  () => (billing.subscription?.limits?.analysis_types as string[] | undefined) ?? ['ats']
)

function isAllowed(type: string) {
  return allowedTypes.value.includes(type)
}

const jobDescriptionValid = computed(
  () => selectedType.value !== 'comparison' || jobDescription.value.trim().length >= 50
)

const canSubmit = computed(
  () => !!selectedType.value && !analysisStore.creating && jobDescriptionValid.value
)

onMounted(() => {
  billing.fetchSubscription().catch(() => undefined)
})

async function handleAnalyze() {
  error.value = ''
  upgrade.value = null

  try {
    const analysis = await analysisStore.createAnalysis(
      resumeId,
      selectedType.value,
      jobDescription.value.trim() || undefined
    )

    router.push({ name: 'analysis', params: { resumeId, analysisId: analysis.id } })
  } catch (e) {
    error.value = await messageFor(e)
    upgrade.value = (await upgradeActionFor(e)) ?? null
  }
}
</script>
