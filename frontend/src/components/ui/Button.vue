<template>
  <component
    :is="tag"
    :type="tag === 'button' ? (type ?? 'button') : undefined"
    :disabled="tag === 'button' ? disabled : undefined"
    :class="classes"
  >
    <Spinner v-if="loading" :size="size === 'sm' ? '0.8rem' : '0.95rem'" />
    <slot />
    <span v-if="!loading && $slots.icon" class="inline-flex transition-transform duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] group-hover:translate-x-1">
      <slot name="icon" />
    </span>
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Spinner from '@/components/ui/Spinner.vue'

const props = withDefaults(
  defineProps<{
    /** 'primary' carries the brand color — spend it on one action per view. */
    variant?: 'primary' | 'secondary' | 'ghost' | 'destructive'
    size?: 'sm' | 'md' | 'lg'
    tag?: 'button' | 'a' | 'router-link'
    type?: 'button' | 'submit'
    disabled?: boolean
    loading?: boolean
  }>(),
  { variant: 'primary', size: 'md', tag: 'button', disabled: false, loading: false }
)

// Pill-shaped at every size — one consistent silhouette rather than a
// different corner radius per size.
const sizes = {
  sm: 'px-3.5 py-1.5 text-xs gap-1.5 rounded-full',
  md: 'px-5 py-2.5 text-sm gap-2 rounded-full',
  lg: 'px-7 py-3.5 text-base gap-2.5 rounded-full',
}

// A snappier expo-out ease reads as more considered than the default
// linear/ease-in-out — a small thing, but it's most of what separates a
// "designed" button from a default one.
const EASE = 'ease-[cubic-bezier(0.25,1,0.5,1)]'
const LIFT = `transition-[transform,box-shadow] duration-300 ${EASE} hover:-translate-y-0.5 hover:shadow-overlay active:translate-y-0`

// Primary and secondary are the same blue family at two strengths — a
// filled pill and a tinted-outline pill — so they read as related rather
// than as two different button systems. Destructive stays in its own
// (critical) hue on purpose: it should never blend in with the brand.
const variants = {
  primary: `glass-brand text-on-brand ${LIFT}`,
  secondary: `glass-brand-soft text-brand ${LIFT}`,
  ghost: `text-content-muted transition-colors duration-300 ${EASE} hover:text-brand`,
  destructive: `glass-critical text-white ${LIFT}`,
}

const classes = computed(() => [
  'group inline-flex shrink-0 items-center justify-center font-semibold',
  'disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none',
  sizes[props.size],
  variants[props.variant],
])
</script>
