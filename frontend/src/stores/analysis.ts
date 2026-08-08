import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Analysis } from '@/types'
import api from '@/services/api'

export const useAnalysisStore = defineStore('analysis', () => {
  const analyses = ref<Analysis[]>([])
  const currentAnalysis = ref<Analysis | null>(null)
  const loading = ref(false)
  const creating = ref(false)

  const page = ref(1)
  const lastPage = ref(1)
  const total = ref(0)

  async function fetchAnalyses(resumeId: number, targetPage = 1) {
    loading.value = true

    try {
      const { data } = await api.get(`/resumes/${resumeId}/analyses`, {
        params: { page: targetPage },
      })

      analyses.value = data.data
      page.value = data.meta?.current_page ?? targetPage
      lastPage.value = data.meta?.last_page ?? 1
      total.value = data.meta?.total ?? data.data.length
    } finally {
      loading.value = false
    }
  }

  async function createAnalysis(
    resumeId: number,
    type: string,
    jobDescription?: string
  ): Promise<Analysis> {
    creating.value = true

    try {
      const payload: Record<string, string> = { type }
      if (jobDescription) payload.job_description = jobDescription

      const { data } = await api.post(`/resumes/${resumeId}/analyses`, payload)
      analyses.value.unshift(data.data)
      total.value++

      return data.data
    } finally {
      creating.value = false
    }
  }

  async function fetchAnalysis(resumeId: number, analysisId: number) {
    loading.value = true

    try {
      const { data } = await api.get(`/resumes/${resumeId}/analyses/${analysisId}`)
      currentAnalysis.value = data.data
      return data.data
    } finally {
      loading.value = false
    }
  }

  async function deleteAnalysis(resumeId: number, analysisId: number) {
    await api.delete(`/resumes/${resumeId}/analyses/${analysisId}`)
    analyses.value = analyses.value.filter((a) => a.id !== analysisId)
    total.value = Math.max(0, total.value - 1)
  }

  return {
    analyses,
    currentAnalysis,
    loading,
    creating,
    page,
    lastPage,
    total,
    fetchAnalyses,
    createAnalysis,
    fetchAnalysis,
    deleteAnalysis,
  }
})
