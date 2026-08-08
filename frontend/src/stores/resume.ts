import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Resume } from '@/types'
import api from '@/services/api'

export const useResumeStore = defineStore('resume', () => {
  const resumes = ref<Resume[]>([])
  const loading = ref(false)
  const uploading = ref(false)

  // The API paginates; without these the list silently truncated at 15.
  const page = ref(1)
  const lastPage = ref(1)
  const total = ref(0)

  async function fetchResumes(targetPage = 1) {
    loading.value = true

    try {
      const { data } = await api.get('/resumes', { params: { page: targetPage } })
      resumes.value = data.data
      page.value = data.meta?.current_page ?? targetPage
      lastPage.value = data.meta?.last_page ?? 1
      total.value = data.meta?.total ?? data.data.length
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

      resumes.value.unshift(data.data)
      total.value++

      return data.data
    } finally {
      uploading.value = false
    }
  }

  async function deleteResume(id: number) {
    await api.delete(`/resumes/${id}`)
    resumes.value = resumes.value.filter((r) => r.id !== id)
    total.value = Math.max(0, total.value - 1)

    // Pull the next page's first item up so the list does not shrink.
    if (!resumes.value.length && page.value > 1) {
      await fetchResumes(page.value - 1)
    } else if (lastPage.value > 1) {
      await fetchResumes(page.value)
    }
  }

  async function getResume(id: number): Promise<Resume> {
    const { data } = await api.get(`/resumes/${id}`)
    return data.data
  }

  /** Poll a single resume until extraction settles, so the badge resolves itself. */
  async function refreshResume(id: number): Promise<Resume | null> {
    try {
      const resume = await getResume(id)
      const index = resumes.value.findIndex((r) => r.id === id)
      if (index !== -1) resumes.value[index] = resume
      return resume
    } catch {
      return null
    }
  }

  return {
    resumes,
    loading,
    uploading,
    page,
    lastPage,
    total,
    fetchResumes,
    uploadResume,
    deleteResume,
    getResume,
    refreshResume,
  }
})
