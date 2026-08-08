<template>
  <button
    class="rounded-lg p-2 text-content-muted transition hover:bg-surface-muted hover:text-content"
    :aria-label="`Switch to ${resolved === 'dark' ? 'light' : 'dark'} theme`"
    :title="`Switch to ${resolved === 'dark' ? 'light' : 'dark'} theme`"
    @click="toggle"
  >
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path
        v-if="resolved === 'dark'"
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36-.7-.7M6.34 6.34l-.7-.7m12.72 0-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"
      />
      <path
        v-else
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"
      />
    </svg>
  </button>
</template>

<script setup lang="ts">
import { useTheme } from '@/composables/useTheme'

const { resolved, toggle } = useTheme()
</script>
