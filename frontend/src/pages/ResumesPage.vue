<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Resumes</h1>
      <p class="text-gray-600 mt-1">Upload and manage your resumes</p>
    </div>

    <div
      @dragover.prevent="dragging = true"
      @dragleave="dragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-xl p-8 text-center transition',
        dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400',
        resumeStore.uploading ? 'opacity-50 pointer-events-none' : '',
      ]"
    >
      <div v-if="resumeStore.uploading" class="space-y-2">
        <div class="animate-spin w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full mx-auto"></div>
        <p class="text-gray-600">Uploading...</p>
      </div>
      <div v-else class="space-y-2">
        <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="text-gray-600">Drag and drop your resume, or</p>
        <label class="inline-block cursor-pointer text-indigo-600 font-medium hover:text-indigo-700">
          Browse files
          <input
            type="file"
            accept=".pdf,.docx"
            class="hidden"
            @change="handleFileSelect"
          />
        </label>
        <p class="text-sm text-gray-500">PDF or DOCX, max 10MB</p>
      </div>
    </div>

    <div v-if="resumeStore.resumes.length === 0 && !resumeStore.loading" class="text-center py-12 text-gray-500">
      No resumes uploaded yet
    </div>

    <div v-else class="space-y-3">
      <router-link
        v-for="resume in resumeStore.resumes"
        :key="resume.id"
        :to="{ name: 'resume', params: { id: resume.id } }"
        class="block bg-white border border-gray-200 rounded-xl p-4 hover:shadow-sm transition"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ resume.title }}</div>
              <div class="text-sm text-gray-500">{{ resume.size_human }} &middot; {{ formatDate(resume.created_at) }}</div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span
              v-if="resume.text_extracted"
              class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full"
            >
              Text extracted
            </span>
            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
              {{ resume.analyses_count }} analyses
            </span>
            <button
              @click.prevent="handleDelete(resume)"
              class="p-2 text-gray-400 hover:text-red-500 transition"
              title="Delete"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useResumeStore } from '@/stores/resume'
import type { Resume } from '@/types'

const resumeStore = useResumeStore()
const dragging = ref(false)

onMounted(() => {
  resumeStore.fetchResumes()
})

function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  if (input.files?.[0]) {
    resumeStore.uploadResume(input.files[0])
    input.value = ''
  }
}

function handleDrop(event: DragEvent) {
  dragging.value = false
  const file = event.dataTransfer?.files[0]
  if (file) {
    resumeStore.uploadResume(file)
  }
}

async function handleDelete(resume: Resume) {
  if (confirm(`Delete "${resume.title}"?`)) {
    await resumeStore.deleteResume(resume.id)
  }
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}
</script>
