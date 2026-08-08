<template>
  <div class="flex flex-col items-center gap-3 px-6 py-12 text-center">
    <span
      class="grid h-11 w-11 place-items-center rounded-xl"
      :class="tone === 'critical' ? 'bg-critical-soft text-critical' : 'bg-surface-muted text-content-subtle'"
      aria-hidden="true"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
      </svg>
    </span>

    <div class="max-w-sm">
      <p class="font-medium text-content">{{ title }}</p>
      <p v-if="description" class="mt-1 text-sm text-content-muted">{{ description }}</p>
    </div>

    <div v-if="$slots.default" class="mt-1">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    title: string
    description?: string
    tone?: 'neutral' | 'critical'
    icon?: string
  }>(),
  {
    tone: 'neutral',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
  }
)
</script>
