<template>
  <details class="group rounded-xl border border-border bg-surface transition hover:border-border-strong">
    <summary
      class="flex cursor-pointer list-none items-start gap-3 p-4 [&::-webkit-details-marker]:hidden"
    >
      <span
        class="mt-0.5 inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-semibold"
        :class="severity.classes"
      >
        <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true" />
        {{ severity.label }}
      </span>

      <span class="min-w-0 flex-1">
        <span class="block font-medium text-content">{{ feedback.message }}</span>
        <span v-if="feedback.section || feedback.category" class="mt-1 block text-xs text-content-subtle">
          {{ [feedback.section, feedback.category].filter(Boolean).join(' · ') }}
        </span>
      </span>

      <svg
        v-if="feedback.suggestion"
        class="mt-0.5 h-5 w-5 shrink-0 text-content-subtle transition-transform group-open:rotate-180"
        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
      </svg>
    </summary>

    <div v-if="feedback.suggestion" class="border-t border-border px-4 py-3">
      <p class="text-xs font-semibold uppercase tracking-wider text-content-subtle">How to fix it</p>
      <p class="mt-1.5 text-sm leading-relaxed text-content-muted">{{ feedback.suggestion }}</p>
    </div>
  </details>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { AnalysisFeedback } from '@/types'

const props = defineProps<{ feedback: AnalysisFeedback }>()

const severity = computed(() => {
  switch (props.feedback.severity) {
    case 'critical':
      return { label: 'Critical', classes: 'border-critical-border bg-critical-soft text-critical' }
    case 'warning':
      return { label: 'Warning', classes: 'border-warning-border bg-warning-soft text-warning' }
    default:
      return { label: 'Suggestion', classes: 'border-info-border bg-info-soft text-info' }
  }
})
</script>
