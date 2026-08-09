<template>
  <div class="min-h-screen bg-canvas">
    <a
      href="#main"
      class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:shadow-raised"
    >
      Skip to content
    </a>

    <header class="sticky top-0 z-30 border-b-2 border-content bg-surface">
      <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <RouterLink
          to="/dashboard"
          class="flex shrink-0 items-center gap-2 rounded-lg font-display font-semibold tracking-tight text-content"
        >
          <span class="grid h-8 w-8 place-items-center rounded-md border-2 border-content bg-brand text-on-brand">
            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" />
            </svg>
          </span>
          <span>ResumeAI</span>
        </RouterLink>

        <nav class="ml-4 hidden items-center gap-1 md:flex" aria-label="Main">
          <RouterLink
            v-for="link in links"
            :key="link.to"
            :to="link.to"
            class="rounded-lg px-3 py-2 text-sm font-medium text-content-muted transition hover:bg-surface-muted hover:text-content"
            active-class="bg-brand-soft text-brand"
          >
            {{ link.label }}
          </RouterLink>
        </nav>

        <div class="ml-auto flex items-center gap-1">
          <ThemeToggle />

          <!-- Account menu (desktop) -->
          <div v-if="auth.user" ref="menuRef" class="relative hidden md:block">
            <button
              class="flex items-center gap-2 rounded-lg py-1.5 pl-1.5 pr-2 text-sm transition hover:bg-surface-muted"
              :aria-expanded="menuOpen"
              aria-haspopup="menu"
              @click="menuOpen = !menuOpen"
            >
              <Avatar :user="auth.user" class="h-7 w-7" />
              <span class="max-w-32 truncate font-medium text-content">{{ auth.user.name }}</span>
              <svg
                class="h-4 w-4 text-content-subtle transition-transform"
                :class="menuOpen && 'rotate-180'"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
              </svg>
            </button>

            <Transition
              enter-active-class="transition duration-150 ease-out"
              enter-from-class="-translate-y-1 opacity-0"
              leave-active-class="transition duration-100 ease-in"
              leave-to-class="opacity-0"
            >
              <div
                v-if="menuOpen"
                role="menu"
                class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-border bg-surface-raised shadow-overlay"
              >
                <div class="border-b border-border px-4 py-3">
                  <p class="truncate text-sm font-medium text-content">{{ auth.user.name }}</p>
                  <p class="truncate text-xs text-content-muted">{{ auth.user.email }}</p>
                </div>
                <div class="p-1">
                  <RouterLink
                    v-for="link in accountLinks"
                    :key="link.to"
                    :to="link.to"
                    role="menuitem"
                    class="block rounded-lg px-3 py-2 text-sm text-content-muted transition hover:bg-surface-muted hover:text-content"
                    @click="menuOpen = false"
                  >
                    {{ link.label }}
                  </RouterLink>
                  <button
                    role="menuitem"
                    class="block w-full rounded-lg px-3 py-2 text-left text-sm text-critical transition hover:bg-critical-soft"
                    @click="auth.logout()"
                  >
                    Sign out
                  </button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Mobile menu trigger -->
          <button
            class="rounded-lg p-2 text-content-muted transition hover:bg-surface-muted hover:text-content md:hidden"
            :aria-expanded="mobileOpen"
            aria-controls="mobile-nav"
            aria-label="Menu"
            @click="mobileOpen = !mobileOpen"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                :d="mobileOpen ? 'M6 18 18 6M6 6l12 12' : 'M4 7h16M4 12h16M4 17h16'"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile navigation -->
      <Transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="-translate-y-2 opacity-0"
        leave-active-class="transition duration-100 ease-in"
        leave-to-class="opacity-0"
      >
        <nav
          v-if="mobileOpen"
          id="mobile-nav"
          class="border-t border-border bg-surface px-4 py-3 md:hidden"
          aria-label="Main"
        >
          <div class="flex flex-col gap-1">
            <RouterLink
              v-for="link in [...links, ...accountLinks]"
              :key="link.to"
              :to="link.to"
              class="rounded-lg px-3 py-2.5 text-sm font-medium text-content-muted transition hover:bg-surface-muted hover:text-content"
              active-class="bg-brand-soft text-brand"
            >
              {{ link.label }}
            </RouterLink>
          </div>

          <div v-if="auth.user" class="mt-3 flex items-center gap-3 border-t border-border pt-3">
            <Avatar :user="auth.user" class="h-9 w-9" />
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium text-content">{{ auth.user.name }}</p>
              <p class="truncate text-xs text-content-muted">{{ auth.user.email }}</p>
            </div>
            <button
              class="rounded-lg px-3 py-1.5 text-sm font-medium text-critical transition hover:bg-critical-soft"
              @click="auth.logout()"
            >
              Sign out
            </button>
          </div>
        </nav>
      </Transition>
    </header>

    <main id="main" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
      <RouterView />
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount, useTemplateRef } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ThemeToggle from '@/components/ui/ThemeToggle.vue'
import Avatar from '@/components/ui/Avatar.vue'

const auth = useAuthStore()
const route = useRoute()

const menuOpen = ref(false)
const mobileOpen = ref(false)
const menuRef = useTemplateRef<HTMLElement>('menuRef')

const links = [
  { to: '/dashboard', label: 'Dashboard' },
  { to: '/resumes', label: 'Resumes' },
]

const accountLinks = [
  { to: '/billing', label: 'Billing' },
  { to: '/settings', label: 'Settings' },
]

// Navigating should never leave a menu hanging open over the new page.
watch(() => route.fullPath, () => {
  menuOpen.value = false
  mobileOpen.value = false
})

function onPointerDown(event: PointerEvent) {
  if (menuOpen.value && !menuRef.value?.contains(event.target as Node)) {
    menuOpen.value = false
  }
}

function onKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    menuOpen.value = false
    mobileOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', onPointerDown)
  document.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onPointerDown)
  document.removeEventListener('keydown', onKeydown)
})
</script>
