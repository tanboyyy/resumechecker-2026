<template>
  <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex min-w-0 items-start gap-3">
      <RouterLink
        v-if="backTo"
        :to="backTo"
        class="mt-1 shrink-0 rounded-lg p-1.5 text-content-subtle transition hover:bg-surface-muted hover:text-content"
        :aria-label="backLabel ?? 'Back'"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
        </svg>
      </RouterLink>

      <div class="min-w-0">
        <p v-if="eyebrow" class="text-xs font-medium uppercase tracking-wider text-content-subtle">
          {{ eyebrow }}
        </p>
        <h1 class="truncate text-2xl font-semibold tracking-tight text-content">
          <slot name="title">{{ title }}</slot>
        </h1>
        <p v-if="description || $slots.description" class="mt-1 text-sm text-content-muted">
          <slot name="description">{{ description }}</slot>
        </p>
      </div>
    </div>

    <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import type { RouteLocationRaw } from 'vue-router'

defineProps<{
  title?: string
  eyebrow?: string
  description?: string
  backTo?: RouteLocationRaw
  backLabel?: string
}>()
</script>
