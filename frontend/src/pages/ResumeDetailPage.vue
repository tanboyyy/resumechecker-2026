<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <router-link to="/resumes" class="text-gray-500 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">{{ resume?.title }}</h1>
        <p class="text-gray-600 mt-1">{{ resume?.original_filename }} &middot; {{ resume?.size_human }}</p>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

    <template v-else-if="resume">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <!-- PDF Viewer -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">PDF Preview</h2>
              <button
                @click="handleDownload"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
              >
                Download
              </button>
            </div>
            <div class="w-full" style="height: 700px;">
              <iframe
                v-if="resume.mime_type?.includes('pdf')"
                :src="pdfViewUrl"
                class="w-full h-full border-0"
                title="PDF Preview"
              ></iframe>
              <div v-else class="flex items-center justify-center h-full text-gray-400">
                <div class="text-center">
                  <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="text-sm">Preview not available for this file type</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Extracted Text (collapsible) -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <button
              @click="showExtractedText = !showExtractedText"
              class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition"
            >
              <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold text-gray-900">Extracted Text</h2>
                <span
                  v-if="resume.text_extracted"
                  class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full"
                >
                  Ready
                </span>
                <span
                  v-else
                  class="px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700 rounded-full"
                >
                  Processing...
                </span>
              </div>
              <svg
                :class="['w-5 h-5 text-gray-400 transition-transform', showExtractedText ? 'rotate-180' : '']"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-show="showExtractedText" class="px-6 pb-6">
              <div v-if="resume.text_extracted && resume.extracted_text" class="text-sm text-gray-700 whitespace-pre-wrap max-h-96 overflow-y-auto border border-gray-100 rounded-lg p-4 bg-gray-50 font-mono leading-relaxed">
                {{ resume.extracted_text }}
              </div>
              <div v-else class="text-gray-400 italic text-sm py-4 text-center">
                Text is being extracted from your resume...
              </div>
            </div>
          </div>

          <!-- Analysis History -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Analysis History</h2>
              <router-link
                :to="{ name: 'analyze', params: { id: resume.id } }"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
              >
                Run new analysis
              </router-link>
            </div>
            <div v-if="analyses.length === 0" class="text-center py-8 text-gray-500">
              No analyses yet
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="analysis in analyses"
                :key="analysis.id"
                class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition"
              >
                <div class="flex items-center gap-3">
                  <div :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                    analysis.status === 'completed' ? 'bg-green-100 text-green-700' :
                    analysis.status === 'failed' ? 'bg-red-100 text-red-700' :
                    'bg-gray-100 text-gray-500'
                  ]">
                    {{ analysis.ats_score ?? '--' }}
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 capitalize">{{ analysis.type }} Analysis</div>
                    <div class="text-xs text-gray-500">{{ formatDate(analysis.created_at) }}</div>
                  </div>
                </div>
                <router-link
                  :to="{ name: 'analysis', params: { resumeId: resume.id, analysisId: analysis.id } }"
                  class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                  View
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-3">Actions</h3>
            <div class="space-y-2">
              <router-link
                :to="{ name: 'analyze', params: { id: resume.id } }"
                class="block w-full text-center bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition"
              >
                Run Analysis
              </router-link>
              <button
                @click="handleDownload"
                class="block w-full text-center bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition"
              >
                Download
              </button>
              <button
                @click="handleDelete"
                class="block w-full text-center bg-white border border-red-200 text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-red-50 transition"
              >
                Delete
              </button>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-gray-500">Status</dt>
                <dd>
                  <span :class="[
                    'px-2 py-0.5 rounded-full text-xs font-medium',
                    resume.text_extracted ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'
                  ]">
                    {{ resume.text_extracted ? 'Ready' : 'Processing' }}
                  </span>
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Analyses</dt>
                <dd class="text-gray-900">{{ resume.analyses_count }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Uploaded</dt>
                <dd class="text-gray-900">{{ formatDate(resume.created_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useResumeStore } from '@/stores/resume'
import { useAnalysisStore } from '@/stores/analysis'
import type { Resume, Analysis } from '@/types'

const route = useRoute()
const router = useRouter()
const resumeStore = useResumeStore()
const analysisStore = useAnalysisStore()

const resume = ref<Resume | null>(null)
const analyses = ref<Analysis[]>([])
const loading = ref(true)
const showExtractedText = ref(false)

const pdfViewUrl = computed(() => {
  if (!resume.value) return ''
  const base = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  return `${base}/resumes/${resume.value.id}/view`
})

onMounted(async () => {
  const id = Number(route.params.id)
  try {
    resume.value = await resumeStore.getResume(id)
    await analysisStore.fetchAnalyses(id)
    analyses.value = analysisStore.analyses
  } finally {
    loading.value = false
  }
})

function handleDownload() {
  if (resume.value) {
    const base = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
    window.open(`${base}/resumes/${resume.value.id}/download`, '_blank')
  }
}

async function handleDelete() {
  if (resume.value && confirm(`Delete "${resume.value.title}"?`)) {
    await resumeStore.deleteResume(resume.value.id)
    router.push('/resumes')
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
