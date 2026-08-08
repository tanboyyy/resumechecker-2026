<template>
  <div
    class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
    role="region"
    aria-label="Notifications"
  >
    <TransitionGroup
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-2 opacity-0"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in toasts.toasts"
        :key="toast.id"
        :class="[
          'pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border px-4 py-3 shadow-overlay',
          tone[toast.tone].shell,
        ]"
        :role="toast.tone === 'error' ? 'alert' : 'status'"
      >
        <svg
          class="mt-0.5 h-5 w-5 shrink-0"
          :class="tone[toast.tone].icon"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path stroke-linecap="round" stroke-linejoin="round" :d="tone[toast.tone].path" />
        </svg>

        <div class="flex-1 text-sm">
          <p class="leading-snug">{{ toast.message }}</p>
          <RouterLink
            v-if="toast.action"
            :to="toast.action.to"
            class="mt-1 inline-block font-semibold underline underline-offset-2"
            @click="toasts.dismiss(toast.id)"
          >
            {{ toast.action.label }}
          </RouterLink>
        </div>

        <button
          class="shrink-0 rounded p-0.5 opacity-60 transition hover:opacity-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-current"
          :aria-label="`Dismiss: ${toast.message}`"
          @click="toasts.dismiss(toast.id)"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useToastStore } from '@/stores/toast'

const toasts = useToastStore()

const tone = {
  success: {
    shell: 'border-success-border bg-success-soft text-content',
    icon: 'text-success',
    path: 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
  },
  error: {
    shell: 'border-critical-border bg-critical-soft text-content',
    icon: 'text-critical',
    path: 'M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z',
  },
  info: {
    shell: 'border-border bg-surface-raised text-content',
    icon: 'text-content-subtle',
    path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
  },
} as const
</script>
