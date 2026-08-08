<template>
  <figure class="flex flex-col items-center gap-3">
    <div class="relative" :style="{ width: `${size}px`, height: `${size}px` }">
      <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`" role="img" :aria-label="ariaLabel">
        <!-- Track -->
        <circle
          :cx="center" :cy="center" :r="radius"
          fill="none"
          stroke="var(--surface-muted)"
          :stroke-width="strokeWidth"
        />
        <!-- Value -->
        <circle
          v-if="score !== null"
          :cx="center" :cy="center" :r="radius"
          fill="none"
          :stroke="band.color"
          :stroke-width="strokeWidth"
          stroke-linecap="round"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="offset"
          :transform="`rotate(-90 ${center} ${center})`"
          class="[transition:stroke-dashoffset_700ms_ease-out] motion-reduce:transition-none"
        />
      </svg>

      <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span v-if="score !== null" class="tabular text-4xl font-semibold tracking-tight text-content">
          {{ displayed }}
        </span>
        <span v-else class="text-2xl font-semibold text-content-subtle">—</span>
        <span class="mt-0.5 text-xs font-medium text-content-muted">{{ label }}</span>
      </div>
    </div>

    <figcaption
      class="rounded-full px-3 py-1 text-xs font-semibold"
      :class="band.classes"
    >
      {{ band.label }}
    </figcaption>
  </figure>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue'
import { scoreBand } from '@/services/score'

const props = withDefaults(
  defineProps<{
    /** null means "not available", which is not the same as zero. */
    score: number | null
    size?: number
    strokeWidth?: number
    label?: string
  }>(),
  { size: 168, strokeWidth: 12, label: 'Score' }
)

const center = computed(() => props.size / 2)
const radius = computed(() => center.value - props.strokeWidth / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)

const band = computed(() => scoreBand(props.score))

const offset = computed(() => {
  const value = Math.max(0, Math.min(100, props.score ?? 0))
  return circumference.value * (1 - value / 100)
})

const ariaLabel = computed(() =>
  props.score === null
    ? `${props.label} not available`
    : `${props.label}: ${props.score} out of 100, ${band.value.label}`
)

// Count up to the score so the number and the arc arrive together.
const displayed = ref(0)

function animate() {
  const target = props.score ?? 0

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    displayed.value = target
    return
  }

  const start = performance.now()
  const from = displayed.value

  const step = (now: number) => {
    const progress = Math.min(1, (now - start) / 700)
    // Ease out, so it settles rather than stopping dead.
    displayed.value = Math.round(from + (target - from) * (1 - (1 - progress) ** 3))
    if (progress < 1) requestAnimationFrame(step)
  }

  requestAnimationFrame(step)
}

onMounted(animate)
watch(() => props.score, animate)
</script>
