<template>
  <div class="flex min-h-[60vh] items-center justify-center">
    <div class="max-w-md text-center">
      <p class="text-sm font-semibold uppercase tracking-wider text-brand">Page not found</p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-content">
        We couldn't find that page
      </h1>
      <p class="mt-3 text-content-muted">
        The link may be out of date, or the resume or analysis it pointed to may have been deleted.
      </p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <RouterLink
          :to="auth.user ? '/dashboard' : '/'"
          class="rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-on-brand transition hover:bg-brand-hover"
        >
          {{ auth.user ? 'Back to dashboard' : 'Back to home' }}
        </RouterLink>
        <RouterLink
          v-if="auth.user"
          to="/resumes"
          class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-content transition hover:bg-surface-muted"
        >
          View resumes
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>
