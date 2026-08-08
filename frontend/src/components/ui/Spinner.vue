<template>
  <span
    class="inline-block animate-spin rounded-full border-2 border-current border-t-transparent align-[-0.125em]"
    :style="{ width: size, height: size }"
    role="status"
    :aria-label="label"
  />
</template>

<script setup lang="ts">
withDefaults(defineProps<{ size?: string; label?: string }>(), {
  size: '1rem',
  label: 'Loading',
})
</script>
