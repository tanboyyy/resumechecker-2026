<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'resume', params: { id: resumeId } }" class="text-gray-500 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Analysis Results</h1>
        <p class="text-gray-600 mt-1 capitalize">{{ analysis?.type ?? 'Analysis' }}</p>
      </div>
      <button
        v-if="analysis?.status === 'completed'"
        @click="handleExport"
        :disabled="exporting"
        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition disabled:opacity-50"
      >
        {{ exporting ? 'Generating...' : 'Export PDF' }}
      </button>
    </div>

    <div v-if="loading && !analysis" class="text-center py-12">
      <div class="animate-spin w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full mx-auto"></div>
      <p class="text-gray-500 mt-2">Loading analysis...</p>
    </div>

    <div v-else-if="isPolling" class="text-center py-12">
      <div class="animate-spin w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full mx-auto"></div>
      <p class="text-gray-500 mt-2">Analysis in progress... (checking every 3s)</p>
      <p class="text-sm text-gray-400 mt-1">This will update automatically when complete</p>
    </div>

    <div v-else-if="analysis?.status === 'failed'" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
      <p class="text-red-700 font-medium">Analysis failed</p>
      <p class="text-red-600 text-sm mt-1">{{ analysis.error_message }}</p>
      <router-link :to="{ name: 'analyze', params: { id: resumeId } }" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-700">
        Try again
      </router-link>
    </div>

    <template v-else-if="analysis">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <ScoreGauge :score="analysis.ats_score ?? 0" :size="160" :stroke-width="10" />
            <div class="mt-4 text-sm text-gray-500">
              {{ feedbackSummary.critical }} critical &middot;
              {{ feedbackSummary.warning }} warnings &middot;
              {{ feedbackSummary.info }} suggestions
            </div>
          </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Feedback</h2>
            <div v-if="analysis.feedback.length === 0" class="text-center py-8 text-gray-500">
              No feedback items
            </div>
            <div v-else class="space-y-3">
              <FeedbackItem
                v-for="item in analysis.feedback"
                :key="item.id"
                :feedback="item"
              />
            </div>
          </div>

          <div v-if="analysis.raw_response" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Summary</h2>
            <div class="text-sm text-gray-700 whitespace-pre-wrap">
              {{ formatRawResponse(analysis.raw_response) }}
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAnalysisStore } from '@/stores/analysis'
import { useAnalysisPolling } from '@/composables/useAnalysisPolling'
import ScoreGauge from '@/components/charts/ScoreGauge.vue'
import FeedbackItem from '@/components/analysis/FeedbackItem.vue'

const route = useRoute()
const analysisStore = useAnalysisStore()

const resumeId = Number(route.params.resumeId)
const analysisId = Number(route.params.analysisId)
const loading = ref(true)
const exporting = ref(false)

const analysis = computed(() => analysisStore.currentAnalysis)

const { status: pollingStatus, polling: isPolling, startPolling, stopPolling } = useAnalysisPolling(resumeId, analysisId)

const feedbackSummary = computed(() => {
  if (!analysis.value) return { critical: 0, warning: 0, info: 0, success: 0 }
  return analysis.value.feedback.reduce(
    (acc, item) => {
      acc[item.severity] = (acc[item.severity] || 0) + 1
      return acc
    },
    { critical: 0, warning: 0, info: 0, success: 0 } as Record<string, number>
  )
})

onMounted(async () => {
  await analysisStore.fetchAnalysis(resumeId, analysisId)
  loading.value = false

  if (analysis.value && (analysis.value.status === 'pending' || analysis.value.status === 'processing')) {
    startPolling()
  }
})

async function handleExport() {
  exporting.value = true
  try {
    window.open(`/api/resumes/${resumeId}/analyses/${analysisId}/export`, '_blank')
  } finally {
    exporting.value = false
  }
}

function formatRawResponse(response: Record<string, unknown>): string {
  if (response.summary) return String(response.summary)
  if (response.overall) return String(response.overall)
  return JSON.stringify(response, null, 2)
}
</script>
