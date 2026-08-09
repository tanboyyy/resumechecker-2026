<template>
  <component
    :is="tag"
    :type="tag === 'button' ? (type ?? 'button') : undefined"
    :disabled="tag === 'button' ? disabled : undefined"
    :class="classes"
  >
    <Spinner v-if="loading" :size="size === 'sm' ? '0.8rem' : '0.95rem'" />
    <slot v-else name="icon" />
    <slot />
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

const sizes = {
  sm: 'px-3 py-1.5 text-xs gap-1.5 rounded-md',
  md: 'px-4 py-2.5 text-sm gap-2 rounded-md',
  lg: 'px-6 py-3.5 text-base gap-2.5 rounded-lg',
}

// Floating glass rather than a boxed, hard-bordered fill: translucent,
// blurred, thin border, with a gentle lift on hover instead of a press.
const LIFT = 'transition-[transform,box-shadow] duration-150 hover:-translate-y-0.5 hover:shadow-overlay active:translate-y-0'

const variants = {
  primary: `glass-brand text-on-brand ${LIFT}`,
  secondary: `glass text-content ${LIFT}`,
  ghost: 'text-content-muted transition duration-150 hover:bg-surface-muted hover:text-content',
  destructive: `glass-critical text-white ${LIFT}`,
}

const classes = computed(() => [
  'inline-flex shrink-0 items-center justify-center font-semibold',
  'disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none',
  sizes[props.size],
  variants[props.variant],
])
</script>
