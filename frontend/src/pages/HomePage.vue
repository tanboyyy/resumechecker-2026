<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center">
    <div class="text-center space-y-6">
      <h1 class="text-5xl font-bold text-gray-900">ResumeAI</h1>
      <p class="text-xl text-gray-600 max-w-md">
        AI-powered resume analysis to beat the ATS and land your dream job.
      </p>
      <div v-if="auth.user" class="space-y-4">
        <p class="text-gray-700">Welcome back, {{ auth.user.name }}!</p>
        <router-link
          to="/dashboard"
          class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition"
        >
          Go to Dashboard
        </router-link>
      </div>
      <div v-else>
        <router-link
          to="/login"
          class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition"
        >
          Get Started
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>
