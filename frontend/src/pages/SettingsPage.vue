<template>
  <div class="max-w-2xl space-y-6">
    <PageHeader title="Settings" description="Your account and preferences." />

    <section class="rounded-xl border border-border bg-surface shadow-card">
      <h2 class="border-b border-border px-5 py-4 font-semibold text-content">Profile</h2>

      <div v-if="auth.user" class="space-y-5 p-5">
        <div class="flex items-center gap-4">
          <Avatar :user="auth.user" class="h-14 w-14 text-base" />
          <div class="min-w-0">
            <p class="truncate font-medium text-content">{{ auth.user.name }}</p>
            <p class="truncate text-sm text-content-muted">{{ auth.user.email }}</p>
          </div>
        </div>

        <dl class="grid gap-4 border-t border-border pt-5 sm:grid-cols-2">
          <div>
            <dt class="text-sm text-content-muted">Signed in with</dt>
            <dd class="mt-0.5 font-medium capitalize text-content">{{ auth.user.provider }}</dd>
          </div>
          <div>
            <dt class="text-sm text-content-muted">Plan</dt>
            <dd class="mt-0.5 font-medium capitalize text-content">
              {{ billing.subscription?.plan ?? 'Free' }}
            </dd>
          </div>
        </dl>
      </div>
    </section>

    <section class="rounded-xl border border-border bg-surface shadow-card">
      <h2 class="border-b border-border px-5 py-4 font-semibold text-content">Appearance</h2>

      <div class="p-5">
        <p class="text-sm text-content-muted">Theme</p>
        <div class="mt-2 inline-flex rounded-lg border border-border p-1" role="radiogroup" aria-label="Theme">
          <button
            v-for="option in themeOptions"
            :key="option.value"
            role="radio"
            :aria-checked="preference === option.value"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition"
            :class="preference === option.value
              ? 'bg-brand text-on-brand'
              : 'text-content-muted hover:text-content'"
            @click="setPreference(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>
    </section>

    <section class="rounded-xl border border-border bg-surface shadow-card">
      <h2 class="border-b border-border px-5 py-4 font-semibold text-content">Account</h2>

      <div class="divide-y divide-border">
        <RouterLink to="/billing" class="flex items-center justify-between gap-4 p-5 transition hover:bg-surface-muted">
          <span>
            <span class="block font-medium text-content">Billing and subscription</span>
            <span class="mt-0.5 block text-sm text-content-muted">Manage your plan and payment method</span>
          </span>
          <svg class="h-5 w-5 shrink-0 text-content-subtle" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
          </svg>
        </RouterLink>

        <button class="flex w-full items-center justify-between gap-4 p-5 text-left transition hover:bg-critical-soft" @click="handleLogout">
          <span>
            <span class="block font-medium text-critical">Sign out</span>
            <span class="mt-0.5 block text-sm text-content-muted">You'll need to sign in again to continue</span>
          </span>
        </button>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useBillingStore } from '@/stores/billing'
import { useTheme, type ThemePreference } from '@/composables/useTheme'
import { confirm } from '@/composables/useConfirm'
import PageHeader from '@/components/ui/PageHeader.vue'
import Avatar from '@/components/ui/Avatar.vue'

const auth = useAuthStore()
const billing = useBillingStore()
const { preference, setPreference } = useTheme()

const themeOptions: Array<{ value: ThemePreference; label: string }> = [
  { value: 'light', label: 'Light' },
  { value: 'dark', label: 'Dark' },
  { value: 'system', label: 'System' },
]

onMounted(() => {
  billing.fetchSubscription().catch(() => undefined)
})

async function handleLogout() {
  const confirmed = await confirm({
    title: 'Sign out?',
    description: 'You will need to sign in with Google again.',
    confirmLabel: 'Sign out',
    tone: 'brand',
  })

  if (confirmed) auth.logout()
}
</script>
