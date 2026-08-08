<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="state"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 backdrop-blur-[2px] sm:items-center"
        @click.self="resolve(false)"
      >
        <div
          ref="panel"
          role="alertdialog"
          aria-modal="true"
          :aria-labelledby="titleId"
          :aria-describedby="state.description ? bodyId : undefined"
          class="w-full max-w-md rounded-2xl border border-border bg-surface-raised p-6 shadow-overlay"
        >
          <div class="flex gap-4">
            <span
              class="grid h-10 w-10 shrink-0 place-items-center rounded-full"
              :class="state.tone === 'critical' ? 'bg-critical-soft text-critical' : 'bg-brand-soft text-brand'"
              aria-hidden="true"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              </svg>
            </span>

            <div class="min-w-0 flex-1">
              <h2 :id="titleId" class="text-base font-semibold text-content">{{ state.title }}</h2>
              <p v-if="state.description" :id="bodyId" class="mt-1.5 text-sm text-content-muted">
                {{ state.description }}
              </p>
            </div>
          </div>

          <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button
              class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-content transition hover:bg-surface-muted"
              @click="resolve(false)"
            >
              {{ state.cancelLabel ?? 'Cancel' }}
            </button>
            <button
              ref="confirmButton"
              class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition"
              :class="state.tone === 'critical' ? 'bg-critical hover:opacity-90' : 'bg-brand text-on-brand hover:bg-brand-hover'"
              @click="resolve(true)"
            >
              {{ state.confirmLabel ?? 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { nextTick, onMounted, onBeforeUnmount, useId, useTemplateRef, watch } from 'vue'
import { confirmState, resolveConfirm } from '@/composables/useConfirm'

const state = confirmState
const titleId = useId()
const bodyId = useId()
const confirmButton = useTemplateRef<HTMLButtonElement>('confirmButton')

function resolve(answer: boolean) {
  resolveConfirm(answer)
}

function onKeydown(event: KeyboardEvent) {
  if (state.value && event.key === 'Escape') resolve(false)
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))

// Move focus into the dialog so keyboard users are not left behind it.
watch(state, async (value) => {
  if (value) {
    await nextTick()
    confirmButton.value?.focus()
  }
})
</script>
