import { defineStore } from 'pinia'
import { ref } from 'vue'

export type ToastTone = 'success' | 'error' | 'info'

export interface Toast {
  id: number
  tone: ToastTone
  message: string
  /** Optional in-app link, e.g. sending someone to Billing after a plan limit. */
  action?: { label: string; to: string }
}

const DISMISS_AFTER = { success: 4000, info: 5000, error: 8000 } as const

let nextId = 0

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<Toast[]>([])

  function push(tone: ToastTone, message: string, action?: Toast['action']) {
    const id = ++nextId
    toasts.value.push({ id, tone, message, action })
    setTimeout(() => dismiss(id), DISMISS_AFTER[tone])
    return id
  }

  function dismiss(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return {
    toasts,
    dismiss,
    success: (message: string, action?: Toast['action']) => push('success', message, action),
    error: (message: string, action?: Toast['action']) => push('error', message, action),
    info: (message: string, action?: Toast['action']) => push('info', message, action),
  }
})
