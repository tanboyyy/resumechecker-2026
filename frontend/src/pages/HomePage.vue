<template>
  <div class="min-h-screen bg-white">
    <!-- Nav -->
    <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-md z-50 border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <span class="text-xl font-bold text-gray-900">ResumeAI</span>
          </div>
          <div class="flex items-center gap-6">
            <a href="#features" class="text-sm text-gray-600 hover:text-gray-900">Features</a>
            <a href="#about" class="text-sm text-gray-600 hover:text-gray-900">About</a>
            <router-link v-if="auth.user" to="/dashboard" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
              Dashboard
            </router-link>
            <router-link v-else to="/login" class="text-sm font-medium bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
              Get Started
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-sm font-medium mb-6">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          AI-Powered Resume Analysis
        </div>
        <h1 class="text-5xl sm:text-6xl font-bold text-gray-900 leading-tight mb-6">
          Beat the ATS.<br>
          <span class="text-indigo-600">Land the interview.</span>
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-10">
          Upload your resume and get instant AI-powered feedback on ATS compatibility,
          content quality, and formatting — so you can fix what matters before you apply.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            :to="auth.user ? '/dashboard' : '/login'"
            class="inline-flex items-center justify-center gap-2 bg-indigo-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200"
          >
            Analyze My Resume
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </router-link>
          <a href="#how-it-works" class="inline-flex items-center justify-center gap-2 bg-white text-gray-700 px-8 py-4 rounded-xl font-semibold text-lg border border-gray-300 hover:bg-gray-50 transition">
            See How It Works
          </a>
        </div>
      </div>
    </section>

    <!-- Screenshot / Visual -->
    <section class="pb-20 px-4">
      <div class="max-w-5xl mx-auto">
        <div class="bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-800">
          <div class="flex items-center gap-2 px-4 py-3 bg-gray-800">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-gray-500 text-sm ml-4">ResumeAI Dashboard</span>
          </div>
          <div class="p-8 grid grid-cols-3 gap-6">
            <div class="bg-gray-800 rounded-xl p-6">
              <div class="text-sm text-gray-400">ATS Score</div>
              <div class="text-4xl font-bold text-green-400 mt-1">87</div>
              <div class="mt-3 h-2 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-green-400 rounded-full" style="width: 87%"></div>
              </div>
            </div>
            <div class="bg-gray-800 rounded-xl p-6">
              <div class="text-sm text-gray-400">Feedback Items</div>
              <div class="text-4xl font-bold text-yellow-400 mt-1">12</div>
              <div class="text-sm text-gray-500 mt-2">3 critical · 5 warnings</div>
            </div>
            <div class="bg-gray-800 rounded-xl p-6">
              <div class="text-sm text-gray-400">Analyses Run</div>
              <div class="text-4xl font-bold text-indigo-400 mt-1">24</div>
              <div class="text-sm text-gray-500 mt-2">this month</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 px-4 bg-gray-50">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Everything you need to get hired</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">Four analysis types powered by GPT-4o, designed to catch what hiring managers and ATS systems look for.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="feature in features" :key="feature.title" class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center mb-4', feature.bgColor]">
              <svg :class="['w-6 h-6', feature.iconColor]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ feature.title }}</h3>
            <p class="text-sm text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 px-4">
      <div class="max-w-5xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">How it works</h2>
          <p class="text-lg text-gray-600">Three steps to a better resume.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-12">
          <div v-for="(step, i) in steps" :key="i" class="text-center">
            <div class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-xl font-bold mx-auto mb-6">
              {{ i + 1 }}
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ step.title }}</h3>
            <p class="text-sm text-gray-600">{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About / Why I Built This -->
    <section id="about" class="py-20 px-4 bg-gray-50">
      <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-10">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Why I built this</h2>
          <div class="space-y-4 text-gray-600 leading-relaxed">
            <p>
              I built ResumeAI as a student project to practice working with AI tools in a real-world context. I wanted to understand how large language models like GPT-4o could be applied to solve a problem that actually matters.
            </p>
            <p>
              The problem I picked: <strong class="text-gray-900">resume optimization</strong>. Every semester, my peers and I stress over whether our resumes will pass automated screening systems. Most of us never get human eyes on our applications — a machine decides first.
            </p>
            <p>
              So I built a tool that gives you that machine's perspective <em>before</em> you hit submit. Upload your resume, pick an analysis type, and get actionable feedback in seconds. It won't guarantee you a job, but it will catch the things that get you filtered out.
            </p>
            <p>
              This project taught me full-stack development, API design, queue systems, payment integration, and how to ship something that works. I hope it helps you too.
            </p>
          </div>
          <div class="mt-8 pt-6 border-t border-gray-200">
            <router-link
              :to="auth.user ? '/dashboard' : '/login'"
              class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition"
            >
              Try it free
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Tech Stack -->
    <section class="py-20 px-4">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Built with</h2>
        <div class="flex flex-wrap justify-center gap-6">
          <div v-for="tech in techStack" :key="tech" class="px-5 py-3 bg-gray-100 rounded-xl text-sm font-medium text-gray-700">
            {{ tech }}
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-4 bg-indigo-600">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to improve your resume?</h2>
        <p class="text-lg text-indigo-200 mb-8">Upload your resume and get AI-powered feedback in seconds.</p>
        <router-link
          :to="auth.user ? '/dashboard' : '/login'"
          class="inline-flex items-center gap-2 bg-white text-indigo-600 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-indigo-50 transition shadow-lg"
        >
          Get Started Free
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </router-link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-200 py-8 px-4">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-indigo-600 rounded-md flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span class="font-semibold text-gray-900">ResumeAI</span>
        </div>
        <p class="text-sm text-gray-500">Student project — built to learn, designed to help.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const features = [
  {
    title: 'ATS Optimization',
    description: 'Check if your resume passes Applicant Tracking Systems. Get a compatibility score and specific fixes.',
    icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
    bgColor: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'Content Review',
    description: 'Get detailed feedback on your resume content, bullet points, action verbs, and impact statements.',
    icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    bgColor: 'bg-purple-50',
    iconColor: 'text-purple-600',
  },
  {
    title: 'Formatting Check',
    description: 'Review your resume structure, consistency, section order, and visual presentation.',
    icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    bgColor: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
  {
    title: 'Job Comparison',
    description: 'Compare your resume against a specific job description for tailored improvement suggestions.',
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    bgColor: 'bg-green-50',
    iconColor: 'text-green-600',
  },
]

const steps = [
  {
    title: 'Upload your resume',
    description: 'Drag and drop your PDF or DOCX file. We extract the text automatically.',
  },
  {
    title: 'Choose an analysis',
    description: 'Pick from ATS check, content review, formatting, or job description comparison.',
  },
  {
    title: 'Get actionable feedback',
    description: 'Receive a score, prioritized suggestions, and specific fixes you can make today.',
  },
]

const techStack = [
  'Laravel 13',
  'Vue 3',
  'TypeScript',
  'Tailwind CSS',
  'OpenAI GPT-4o',
  'PostgreSQL',
  'Redis',
  'Stripe',
  'Docker',
  'Sanctum',
]
</script>
