<template>
  <span
    class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium"
    :class="variant.classes"
  >
    <Spinner v-if="inProgress" size="0.65rem" :label="variant.label" />
    <span v-else class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true" />
    {{ variant.label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Spinner from '@/components/ui/Spinner.vue'
import type { Analysis } from '@/types'

const props = defineProps<{ status: Analysis['status'] }>()

const inProgress = computed(() => props.status === 'pending' || props.status === 'processing')

const variant = computed(() => {
  switch (props.status) {
    case 'completed':
      return { label: 'Complete', classes: 'border-success-border bg-success-soft text-success' }
    case 'failed':
      return { label: 'Failed', classes: 'border-critical-border bg-critical-soft text-critical' }
    case 'processing':
      return { label: 'Analysing', classes: 'border-info-border bg-info-soft text-info' }
    default:
      return { label: 'Queued', classes: 'border-border bg-surface-muted text-content-muted' }
  }
})
</script>
