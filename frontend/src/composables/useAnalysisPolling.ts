import { ref, onUnmounted } from 'vue'
import api from '@/services/api'

export function useAnalysisPolling(
  resumeId: number,
  analysisId: number,
  onComplete?: () => void,
) {
  const status = ref<string>('pending')
  const score = ref<number | null>(null)
  const error = ref<string | null>(null)
  const polling = ref(false)
  let interval: ReturnType<typeof setInterval> | null = null

  async function checkStatus() {
    try {
      const { data } = await api.get(`/resumes/${resumeId}/analyses/${analysisId}/status`)
      status.value = data.status
      score.value = data.ats_score
      error.value = data.error_message

      if (data.status === 'completed' || data.status === 'failed') {
        stopPolling()
        onComplete?.()
      }
    } catch {
      stopPolling()
    }
  }

  function startPolling(intervalMs = 3000) {
    polling.value = true
    checkStatus()
    interval = setInterval(checkStatus, intervalMs)
  }

  function stopPolling() {
    polling.value = false
    if (interval) {
      clearInterval(interval)
      interval = null
    }
  }

  onUnmounted(() => {
    stopPolling()
  })

  return { status, score, error, polling, startPolling, stopPolling }
}
