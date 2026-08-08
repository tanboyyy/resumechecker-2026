<template>
  <div class="animate-pulse rounded-lg bg-surface-muted" :style="{ width, height }" aria-hidden="true" />
</template>

<script setup lang="ts">
withDefaults(defineProps<{ width?: string; height?: string }>(), {
  width: '100%',
  height: '1rem',
})
</script>
