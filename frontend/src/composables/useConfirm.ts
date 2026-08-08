import { ref, type Ref } from 'vue'

export interface ConfirmOptions {
  title: string
  description?: string
  confirmLabel?: string
  cancelLabel?: string
  tone?: 'critical' | 'brand'
}

/** The dialog currently being shown, or null. Rendered once by ConfirmDialog. */
export const confirmState: Ref<ConfirmOptions | null> = ref(null)

let pending: ((answer: boolean) => void) | null = null

/**
 * Replaces window.confirm with something that matches the rest of the app and
 * can be styled, focused, and dismissed properly.
 *
 * await confirm({ title: 'Delete this resume?', tone: 'critical' })
 */
export function confirm(options: ConfirmOptions): Promise<boolean> {
  // A second request supersedes the first rather than leaking its promise.
  pending?.(false)

  confirmState.value = { tone: 'critical', ...options }

  return new Promise<boolean>((resolve) => {
    pending = resolve
  })
}

export function resolveConfirm(answer: boolean) {
  confirmState.value = null
  pending?.(answer)
  pending = null
}
