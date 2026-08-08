<template>
  <span
    class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium"
    :class="variant.classes"
    :title="resume.extraction_error ?? undefined"
  >
    <span v-if="resume.extraction_status === 'pending'" class="relative flex h-1.5 w-1.5" aria-hidden="true">
      <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-current opacity-60" />
      <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-current" />
    </span>
    <span v-else class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true" />
    {{ variant.label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Resume } from '@/types'

const props = defineProps<{ resume: Resume }>()

const variant = computed(() => {
  switch (props.resume.extraction_status) {
    case 'completed':
      return { label: 'Ready', classes: 'border-success-border bg-success-soft text-success' }
    case 'failed':
      return { label: "Couldn't read", classes: 'border-critical-border bg-critical-soft text-critical' }
    default:
      return { label: 'Reading…', classes: 'border-warning-border bg-warning-soft text-warning' }
  }
})
</script>
