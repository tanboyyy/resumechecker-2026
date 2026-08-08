<template>
  <nav
    class="flex items-center justify-between gap-4 border-t border-border px-4 py-3"
    :aria-label="`${label} pagination`"
  >
    <p class="text-sm text-content-muted">
      Page <span class="tabular font-medium text-content">{{ page }}</span> of
      <span class="tabular font-medium text-content">{{ lastPage }}</span>
      <span v-if="total" class="hidden sm:inline"> &middot; {{ total }} {{ label }}</span>
    </p>

    <div class="flex gap-2">
      <button
        class="rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-content transition hover:bg-surface-muted disabled:cursor-not-allowed disabled:opacity-40"
        :disabled="page <= 1"
        @click="emit('change', page - 1)"
      >
        Previous
      </button>
      <button
        class="rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-content transition hover:bg-surface-muted disabled:cursor-not-allowed disabled:opacity-40"
        :disabled="page >= lastPage"
        @click="emit('change', page + 1)"
      >
        Next
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    page: number
    lastPage: number
    total?: number
    label?: string
  }>(),
  { label: 'items', total: 0 }
)

const emit = defineEmits<{ change: [page: number] }>()
</script>
