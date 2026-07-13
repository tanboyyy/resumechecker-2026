<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'resume', params: { id: resumeId } }" class="text-gray-500 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Run Analysis</h1>
        <p class="text-gray-600 mt-1">Choose an analysis type</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <button
        v-for="type in analysisTypes"
        :key="type.value"
        @click="selectedType = type.value"
        :class="[
          'p-6 rounded-xl border-2 text-left transition',
          selectedType === type.value
            ? 'border-indigo-500 bg-indigo-50'
            : 'border-gray-200 bg-white hover:border-gray-300',
        ]"
      >
        <div class="flex items-center gap-3 mb-2">
          <component :is="type.icon" class="w-6 h-6" :class="selectedType === type.value ? 'text-indigo-600' : 'text-gray-400'" />
          <h3 class="font-semibold text-gray-900">{{ type.label }}</h3>
        </div>
        <p class="text-sm text-gray-600">{{ type.description }}</p>
      </button>
    </div>

    <div v-if="selectedType === 'comparison'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Job Description</label>
      <textarea
        v-model="jobDescription"
        rows="6"
        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        placeholder="Paste the job description here..."
      />
    </div>

    <div class="flex justify-end">
      <button
        @click="handleAnalyze"
        :disabled="!selectedType || analysisStore.creating"
        :class="[
          'px-6 py-3 rounded-lg font-medium transition',
          selectedType && !analysisStore.creating
            ? 'bg-indigo-600 text-white hover:bg-indigo-700'
            : 'bg-gray-200 text-gray-500 cursor-not-allowed',
        ]"
      >
        {{ analysisStore.creating ? 'Starting...' : 'Run Analysis' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, h } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAnalysisStore } from '@/stores/analysis'

const route = useRoute()
const router = useRouter()
const analysisStore = useAnalysisStore()

const resumeId = Number(route.params.id)
const selectedType = ref<string | null>(null)
const jobDescription = ref('')

const AnalysisIcon = {
  render() {
    return h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
    ])
  }
}

const analysisTypes = [
  {
    value: 'ats',
    label: 'ATS Optimization',
    description: 'Check how well your resume passes Applicant Tracking Systems. Get a score and improvement tips.',
    icon: AnalysisIcon,
  },
  {
    value: 'content',
    label: 'Content Review',
    description: 'Get detailed feedback on your resume content, language, and impact statements.',
    icon: AnalysisIcon,
  },
  {
    value: 'formatting',
    label: 'Formatting Check',
    description: 'Review your resume structure, consistency, and visual presentation.',
    icon: AnalysisIcon,
  },
  {
    value: 'comparison',
    label: 'Job Comparison',
    description: 'Compare your resume against a specific job description for targeted improvements.',
    icon: AnalysisIcon,
  },
]

async function handleAnalyze() {
  if (!selectedType.value) return

  const analysis = await analysisStore.createAnalysis(
    resumeId,
    selectedType.value,
    jobDescription.value || undefined,
  )

  router.push({ name: 'analysis', params: { resumeId, analysisId: analysis.id } })
}
</script>
