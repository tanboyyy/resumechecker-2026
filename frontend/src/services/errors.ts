import axios from 'axios'
import type { Toast } from '@/stores/toast'

interface ApiErrorBody {
  message?: string
  errors?: Record<string, string[]>
  upgrade_url?: string
}

const FALLBACK: Record<number, string> = {
  401: 'Your session has expired. Please sign in again.',
  403: 'You do not have access to that.',
  404: 'We could not find that.',
  413: 'That file is too large to upload.',
  422: 'Please check the details you entered and try again.',
  429: 'Too many requests. Please wait a moment and try again.',
  500: 'Something went wrong on our end. Please try again.',
  503: 'That feature is temporarily unavailable. Please try again shortly.',
}

/**
 * Turn any thrown value into a sentence worth showing a person.
 *
 * Blob responses need special handling: a failed download still arrives as a
 * Blob, so the JSON error inside it has to be read back out rather than saved
 * to disk as though it were the file.
 */
export async function messageFor(error: unknown): Promise<string> {
  if (!axios.isAxiosError(error)) {
    return error instanceof Error ? error.message : 'Something went wrong. Please try again.'
  }

  if (!error.response) {
    return 'We could not reach the server. Check your connection and try again.'
  }

  const body = await readBody(error.response.data)

  if (body?.message) return body.message

  const firstFieldError = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined
  if (firstFieldError) return firstFieldError

  return FALLBACK[error.response.status] ?? 'Something went wrong. Please try again.'
}

/** An upgrade link when the server said this was a plan limit. */
export async function upgradeActionFor(error: unknown): Promise<Toast['action'] | undefined> {
  if (!axios.isAxiosError(error) || !error.response) return undefined

  const body = await readBody(error.response.data)

  return body?.upgrade_url ? { label: 'See plans', to: body.upgrade_url } : undefined
}

async function readBody(data: unknown): Promise<ApiErrorBody | undefined> {
  if (data instanceof Blob) {
    try {
      return JSON.parse(await data.text()) as ApiErrorBody
    } catch {
      return undefined
    }
  }

  return typeof data === 'object' && data !== null ? (data as ApiErrorBody) : undefined
}
