<template>
  <div :class="[
    'rounded-lg border p-4',
    severityClasses[feedback.severity],
  ]">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 mt-0.5">
        <component :is="severityIcon" :class="iconClasses[feedback.severity]" />
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <span class="text-sm font-medium capitalize">{{ feedback.category }}</span>
          <span v-if="feedback.section" class="text-xs px-1.5 py-0.5 rounded bg-white/50">
            {{ feedback.section }}
          </span>
        </div>
        <p class="text-sm">{{ feedback.message }}</p>
        <p v-if="feedback.suggestion" class="text-sm mt-2 opacity-80">
          <strong>Suggestion:</strong> {{ feedback.suggestion }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { h, computed } from 'vue'
import type { AnalysisFeedback } from '@/types'

const props = defineProps<{
  feedback: AnalysisFeedback
}>()

const severityClasses = computed(() => ({
  critical: 'bg-red-50 border-red-200 text-red-800',
  warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
  info: 'bg-blue-50 border-blue-200 text-blue-800',
  success: 'bg-green-50 border-green-200 text-green-800',
}[props.feedback.severity]))

const iconClasses = computed(() => ({
  critical: 'w-5 h-5 text-red-500',
  warning: 'w-5 h-5 text-yellow-500',
  info: 'w-5 h-5 text-blue-500',
  success: 'w-5 h-5 text-green-500',
}[props.feedback.severity]))

const severityIcon = computed(() => {
  const paths: Record<string, string> = {
    critical: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  }
  return h('svg', {
    class: iconClasses.value,
    fill: 'none',
    viewBox: '0 0 24 24',
    'stroke-width': '2',
    stroke: 'currentColor',
  }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      d: paths[props.feedback.severity],
    })
  ])
})
</script>
