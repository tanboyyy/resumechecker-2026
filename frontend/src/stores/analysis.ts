import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Analysis } from '@/types'
import api from '@/services/api'

export const useAnalysisStore = defineStore('analysis', () => {
  const analyses = ref<Analysis[]>([])
  const currentAnalysis = ref<Analysis | null>(null)
  const loading = ref(false)
  const creating = ref(false)

  async function fetchAnalyses(resumeId: number) {
    loading.value = true
    try {
      const { data } = await api.get(`/resumes/${resumeId}/analyses`)
      analyses.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function createAnalysis(resumeId: number, type: string, jobDescription?: string): Promise<Analysis> {
    creating.value = true
    try {
      const payload: Record<string, string> = { type }
      if (jobDescription) payload.job_description = jobDescription

      const { data } = await api.post(`/resumes/${resumeId}/analyses`, payload)
      analyses.value.unshift(data.data)
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
  }

  return { analyses, currentAnalysis, loading, creating, fetchAnalyses, createAnalysis, fetchAnalysis, deleteAnalysis }
})
