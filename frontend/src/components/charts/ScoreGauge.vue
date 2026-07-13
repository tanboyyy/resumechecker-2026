<template>
  <div class="relative inline-flex items-center justify-center">
    <svg :width="size" :height="size" class="transform -rotate-90">
      <circle
        :cx="size / 2"
        :cy="size / 2"
        :r="radius"
        fill="none"
        :stroke="bgColor"
        :stroke-width="strokeWidth"
      />
      <circle
        :cx="size / 2"
        :cy="size / 2"
        :r="radius"
        fill="none"
        :stroke="scoreColor"
        :stroke-width="strokeWidth"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="dashOffset"
        stroke-linecap="round"
        class="transition-all duration-1000 ease-out"
      />
    </svg>
    <div class="absolute flex flex-col items-center">
      <span class="text-2xl font-bold" :class="scoreTextColor">{{ score }}</span>
      <span class="text-xs text-gray-500">{{ label }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  score: number
  maxScore?: number
  size?: number
  strokeWidth?: number
  label?: string
}>(), {
  maxScore: 100,
  size: 120,
  strokeWidth: 8,
  label: 'ATS Score',
})

const radius = computed(() => (props.size - props.strokeWidth) / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const dashOffset = computed(() => {
  const percentage = Math.min(props.score / props.maxScore, 1)
  return circumference.value * (1 - percentage)
})

const scoreColor = computed(() => {
  if (props.score >= 80) return '#22c55e'
  if (props.score >= 60) return '#eab308'
  if (props.score >= 40) return '#f97316'
  return '#ef4444'
})

const scoreTextColor = computed(() => {
  if (props.score >= 80) return 'text-green-600'
  if (props.score >= 60) return 'text-yellow-600'
  if (props.score >= 40) return 'text-orange-600'
  return 'text-red-600'
})

const bgColor = '#e5e7eb'
</script>
