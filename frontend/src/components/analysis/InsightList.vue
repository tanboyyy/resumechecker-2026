<template>
  <section v-if="items.length" class="rounded-xl border border-border bg-surface p-5 shadow-card">
    <h2 class="flex items-center gap-2 font-semibold text-content">
      <span class="grid h-6 w-6 place-items-center rounded-md" :class="variant.badge" aria-hidden="true">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" :d="variant.icon" />
        </svg>
      </span>
      {{ title }}
      <span class="tabular text-sm font-normal text-content-subtle">{{ items.length }}</span>
    </h2>

    <ul class="mt-3 space-y-2">
      <li v-for="(item, i) in items" :key="i" class="flex gap-2.5 text-sm leading-relaxed text-content-muted">
        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" :class="variant.dot" aria-hidden="true" />
        {{ item }}
      </li>
    </ul>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    title: string
    items: string[]
    tone?: 'success' | 'critical' | 'brand'
  }>(),
  { tone: 'brand' }
)

const variant = computed(() => {
  switch (props.tone) {
    case 'success':
      return {
        badge: 'bg-success-soft text-success',
        dot: 'bg-success',
        icon: 'm5 13 4 4L19 7',
      }
    case 'critical':
      return {
        badge: 'bg-critical-soft text-critical',
        dot: 'bg-critical',
        icon: 'M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
      }
    default:
      return {
        badge: 'bg-brand-soft text-brand',
        dot: 'bg-brand',
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
      }
  }
})
</script>
