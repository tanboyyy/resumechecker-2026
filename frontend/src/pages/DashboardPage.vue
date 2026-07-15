<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <router-link to="/dashboard" class="text-xl font-bold text-gray-900">
            ResumeAI
          </router-link>
          <div class="flex items-center gap-4">
            <router-link
              to="/resumes"
              class="text-sm text-gray-600 hover:text-gray-900 transition"
            >
              Resumes
            </router-link>
            <router-link
              to="/billing"
              class="text-sm text-gray-600 hover:text-gray-900 transition"
            >
              Billing
            </router-link>
            <router-link
              to="/settings"
              class="text-sm text-gray-600 hover:text-gray-900 transition"
            >
              Settings
            </router-link>
            <div v-if="auth.user" class="flex items-center gap-3">
              <img
                v-if="auth.user.avatar"
                :src="auth.user.avatar"
                :alt="auth.user.name"
                class="w-8 h-8 rounded-full"
              />
              <span class="text-sm text-gray-700">{{ auth.user.name }}</span>
              <button
                @click="auth.logout"
                class="text-sm text-gray-500 hover:text-gray-700 transition"
              >
                Sign out
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="route.query.upgraded" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-700 font-medium">Your plan has been upgraded! Enjoy your new features.</p>
      </div>

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-1">Manage your resumes and analyses</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-500">Total Resumes</div>
          <div class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalResumes }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-500">Analyses Run</div>
          <div class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalAnalyses }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-500">Plan</div>
          <div class="text-3xl font-bold text-gray-900 mt-1 capitalize">{{ stats.plan }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-sm font-medium text-gray-500">Analyses Left</div>
          <div class="text-3xl font-bold text-gray-900 mt-1">{{ stats.analysesLeft }}</div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <p v-if="stats.totalResumes > 0" class="text-gray-500 mb-4">
          You have {{ stats.totalResumes }} resume{{ stats.totalResumes === 1 ? '' : 's' }}
        </p>
        <p v-else class="text-gray-500 mb-4">No resumes uploaded yet</p>
        <router-link
          to="/resumes"
          class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition"
        >
          {{ stats.totalResumes > 0 ? 'View Resumes' : 'Upload Resume' }}
        </router-link>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useResumeStore } from '@/stores/resume'
import { useBillingStore } from '@/stores/billing'

const route = useRoute()
const auth = useAuthStore()
const resumeStore = useResumeStore()
const billingStore = useBillingStore()

onMounted(() => {
  resumeStore.fetchResumes()
  billingStore.fetchSubscription()
  billingStore.fetchUsage()
})

const stats = computed(() => ({
  totalResumes: resumeStore.resumes.length,
  totalAnalyses: resumeStore.resumes.reduce((sum, r) => sum + r.analyses_count, 0),
  plan: billingStore.subscription?.plan ?? 'Free',
  analysesLeft: billingStore.usage
    ? (billingStore.usage.analysis_limit === -1 ? '∞' : billingStore.usage.analysis_limit - billingStore.usage.analyses_used)
    : '--',
}))
</script>
