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
    /** 'primary' carries the brand gradient — spend it on one action per view. */
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
  sm: 'px-3 py-1.5 text-xs gap-1.5 rounded-lg',
  md: 'px-4 py-2.5 text-sm gap-2 rounded-lg',
  lg: 'px-6 py-3.5 text-base gap-2.5 rounded-xl',
}

const variants = {
  primary:
    'bg-gradient-brand text-on-brand shadow-[0_1px_0_0_rgb(255_255_255_/_0.15)_inset] hover:brightness-110 hover:shadow-glow',
  secondary:
    'border border-border bg-surface text-content hover:bg-surface-muted hover:border-border-strong',
  ghost: 'text-content-muted hover:bg-surface-muted hover:text-content',
  destructive: 'border border-critical-border bg-critical-soft text-critical hover:opacity-90',
}

const classes = computed(() => [
  'inline-flex shrink-0 items-center justify-center font-semibold transition duration-150',
  'disabled:pointer-events-none disabled:opacity-50',
  sizes[props.size],
  variants[props.variant],
])
</script>
