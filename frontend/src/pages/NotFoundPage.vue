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
        <Button tag="router-link" :to="auth.user ? '/dashboard' : '/'">
          {{ auth.user ? 'Back to dashboard' : 'Back to home' }}
        </Button>
        <Button v-if="auth.user" tag="router-link" to="/resumes" variant="secondary">
          View resumes
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'

const auth = useAuthStore()
</script>
