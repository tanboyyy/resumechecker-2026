<template>
  <img
    v-if="user.avatar && !broken"
    :src="user.avatar"
    :alt="''"
    class="shrink-0 rounded-full border border-border object-cover"
    referrerpolicy="no-referrer"
    @error="broken = true"
  />
  <span
    v-else
    class="grid shrink-0 place-items-center rounded-full bg-brand-soft text-xs font-semibold text-brand"
    aria-hidden="true"
  >
    {{ initials }}
  </span>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { User } from '@/types'

const props = defineProps<{ user: User }>()

// Google avatar URLs expire; fall back to initials rather than a broken image.
const broken = ref(false)

const initials = computed(() =>
  props.user.name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('') || '?'
)
</script>
