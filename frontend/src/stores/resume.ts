import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Resume } from '@/types'
import api from '@/services/api'

export const useResumeStore = defineStore('resume', () => {
  const resumes = ref<Resume[]>([])
  const loading = ref(false)
  const uploading = ref(false)

  async function fetchResumes() {
    loading.value = true
    try {
      const { data } = await api.get('/resumes')
      resumes.value = data
    } finally {
      loading.value = false
    }
  }

  async function uploadResume(file: File, title?: string): Promise<Resume> {
    uploading.value = true
    try {
      const formData = new FormData()
      formData.append('file', file)
      if (title) formData.append('title', title)

      const { data } = await api.post('/resumes', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      resumes.value.unshift(data)
      return data
    } finally {
      uploading.value = false
    }
  }

  async function deleteResume(id: number) {
    await api.delete(`/resumes/${id}`)
    resumes.value = resumes.value.filter((r) => r.id !== id)
  }

  async function getResume(id: number): Promise<Resume> {
    const { data } = await api.get(`/resumes/${id}`)
    return data
  }

  return { resumes, loading, uploading, fetchResumes, uploadResume, deleteResume, getResume }
})
