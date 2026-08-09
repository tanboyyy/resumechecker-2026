<template>
  <div class="min-h-screen bg-canvas">
    <!-- Nav + hero: pinned to the dark palette regardless of the site theme —
         a deliberate brand moment, not a light/dark toggle state. Everything
         inside reads its color from the cascaded dark tokens automatically. -->
    <div data-theme="dark">
      <nav class="fixed top-0 z-50 w-full border-b border-border bg-canvas">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
          <div class="flex items-center gap-2.5">
            <span class="grid h-8 w-8 place-items-center rounded-md border-2 border-content bg-brand text-on-brand">
              <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" />
              </svg>
            </span>
            <span class="font-display text-sm font-medium uppercase tracking-[0.2em] text-content">ResumeAI</span>
          </div>

          <div class="flex items-center gap-1 sm:gap-5">
            <a href="#features" class="hidden font-mono text-xs uppercase tracking-wider text-content-muted transition hover:text-content sm:inline-block">
              [Features]
            </a>
            <a href="#how-it-works" class="hidden font-mono text-xs uppercase tracking-wider text-content-muted transition hover:text-content sm:inline-block">
              [Process]
            </a>
            <Button v-if="auth.user" tag="router-link" to="/dashboard" size="sm">Dashboard</Button>
            <Button v-else tag="router-link" to="/login" size="sm">Get started</Button>
          </div>
        </div>
      </nav>

      <section class="bg-canvas pt-32 pb-24 sm:pt-40 sm:pb-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
          <div class="grid gap-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div class="motion-safe:animate-[rise_0.6s_ease-out_backwards]">
              <p class="font-mono text-xs uppercase tracking-wider text-content-muted">
                [ For job seekers, not recruiters ]
              </p>
              <h1 class="mt-5 text-5xl font-semibold leading-[1.05] tracking-tight text-content sm:text-6xl lg:text-[4.25rem]">
                Beat the ATS.
                <br />
                Land the interview.
              </h1>

              <p class="mt-6 max-w-md font-mono text-xs uppercase leading-relaxed tracking-wide text-content-muted">
                ResumeAI reads your resume the way an applicant tracking system
                does, and tells you what to fix before you hit apply.
              </p>

              <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <Button tag="router-link" :to="auth.user ? '/dashboard' : '/login'" size="lg">
                  Analyse my resume
                  <template #icon>
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0-5 5m5-5H6" />
                    </svg>
                  </template>
                </Button>
                <Button tag="a" href="#how-it-works" variant="secondary" size="lg">
                  See how it works
                </Button>
              </div>

              <dl class="mt-12 flex gap-8 border-t border-border pt-6 font-mono text-xs uppercase tracking-wide text-content-subtle">
                <div v-for="stat in stats" :key="stat.label">
                  <dt>{{ stat.label }}</dt>
                  <dd class="mt-1 text-content">{{ stat.value }}</dd>
                </div>
              </dl>
            </div>

            <!-- The real product preview, not a mockup. -->
            <div class="motion-safe:animate-[rise_0.7s_ease-out_0.1s_backwards] lg:justify-self-end">
              <div class="w-full max-w-sm rounded-xl border-2 border-content bg-surface p-5 shadow-bold sm:p-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2 text-sm font-medium text-content">
                    <svg class="h-4 w-4 text-content-subtle" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" />
                    </svg>
                    resume.pdf
                  </div>
                  <span class="rounded-full border border-success-border bg-success-soft px-2 py-0.5 text-xs font-medium text-success">
                    Complete
                  </span>
                </div>

                <div class="mt-5 flex justify-center">
                  <ScoreGauge :score="78" :size="140" :stroke-width="10" label="ATS score" />
                </div>

                <ul class="mt-5 space-y-2 border-t border-border pt-4">
                  <li v-for="row in previewFindings" :key="row.text" class="flex items-start gap-2.5 text-sm">
                    <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full" :class="row.dot" aria-hidden="true" />
                    <span class="text-content-muted">{{ row.text }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Features -->
    <section id="features" class="px-4 py-24 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-6xl">
        <div class="max-w-xl">
          <p class="font-mono text-xs uppercase tracking-wider text-brand">[ What you get ]</p>
          <h2 class="mt-3 text-3xl font-semibold tracking-tight text-content sm:text-4xl">
            Four ways to see your resume the way a hiring pipeline does
          </h2>
        </div>

        <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="feature in features"
            :key="feature.title"
            class="group rounded-xl border-2 border-content bg-surface p-6 transition duration-150 hover:-translate-y-0.5 hover:shadow-bold"
          >
            <span class="grid h-11 w-11 place-items-center rounded-lg border-2 border-content bg-brand text-on-brand">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
              </svg>
            </span>
            <h3 class="mt-4 font-semibold text-content">{{ feature.title }}</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-content-muted">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="border-y-2 border-content bg-surface-muted/60 px-4 py-24 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-5xl">
        <div class="max-w-xl">
          <p class="font-mono text-xs uppercase tracking-wider text-brand">[ Process ]</p>
          <h2 class="mt-3 text-3xl font-semibold tracking-tight text-content sm:text-4xl">Three steps, a few minutes</h2>
        </div>

        <ol class="mt-14 grid gap-10 sm:grid-cols-3">
          <li v-for="(step, i) in steps" :key="step.title" class="relative">
            <span class="font-mono text-sm text-content-subtle">
              {{ String(i + 1).padStart(2, '0') }}
            </span>
            <h3 class="mt-3 font-semibold text-content">{{ step.title }}</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-content-muted">{{ step.description }}</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- About -->
    <section id="about" class="px-4 py-24 sm:px-6 lg:px-8">
      <div class="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
        <div>
          <p class="font-mono text-xs uppercase tracking-wider text-brand">[ Why this exists ]</p>
          <h2 class="mt-3 text-3xl font-semibold tracking-tight text-content">Why I built this</h2>
        </div>

        <div class="space-y-4 text-content-muted">
          <p class="text-lg leading-relaxed text-content">
            I built ResumeAI as a student project to practice working with AI tools in a real-world
            context — and to understand how large language models could be applied to a problem that
            actually matters.
          </p>
          <p class="leading-relaxed">
            The problem I picked: <strong class="font-medium text-content">resume optimisation</strong>.
            Every semester, my peers and I stress over whether our resumes will pass automated
            screening. Most of us never get human eyes on our applications — a machine decides first.
          </p>
          <p class="leading-relaxed">
            So I built a tool that gives you that machine's perspective before you hit submit. Upload
            your resume, pick an analysis type, and get actionable feedback in seconds. It won't
            guarantee you a job, but it will catch the things that get you filtered out.
          </p>
          <RouterLink
            :to="auth.user ? '/dashboard' : '/login'"
            class="inline-flex items-center gap-1.5 pt-2 font-semibold text-brand transition hover:text-brand-hover"
          >
            Try it free
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0-5 5m5-5H6" />
            </svg>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Tech stack -->
    <section class="px-4 pb-24 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-5xl text-center">
        <p class="font-mono text-xs uppercase tracking-wider text-brand">[ Built with ]</p>
        <div class="mt-5 flex flex-wrap justify-center gap-2.5">
          <span
            v-for="tech in techStack"
            :key="tech"
            class="rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium text-content-muted"
          >
            {{ tech }}
          </span>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="relative overflow-hidden px-4 py-20 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-4xl rounded-2xl border-2 border-content bg-brand px-8 py-16 text-center shadow-bold sm:px-16">
        <h2 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
          Ready to see your resume through a screener's eyes?
        </h2>
        <p class="mx-auto mt-3 max-w-md text-white/85">
          Free to start. No credit card required.
        </p>
        <RouterLink
          :to="auth.user ? '/dashboard' : '/login'"
          class="mt-8 inline-flex items-center gap-2 rounded-md border-2 border-content bg-white px-6 py-3 text-sm font-semibold text-brand transition hover:bg-white/90"
        >
          Get started free
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0-5 5m5-5H6" />
          </svg>
        </RouterLink>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t-2 border-content px-4 py-8 sm:px-6 lg:px-8">
      <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 sm:flex-row">
        <div class="flex items-center gap-2">
          <span class="grid h-6 w-6 place-items-center rounded border-2 border-content bg-brand text-on-brand">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" />
            </svg>
          </span>
          <span class="font-display font-semibold text-content">ResumeAI</span>
        </div>
        <p class="text-sm text-content-subtle">Student project — built to learn, designed to help.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'
import ScoreGauge from '@/components/charts/ScoreGauge.vue'

const auth = useAuthStore()

const stats = [
  { label: 'Types', value: '4' },
  { label: 'Turnaround', value: '<1 min' },
  { label: 'Formats', value: 'PDF/DOCX' },
]

const previewFindings = [
  { text: 'Missing a professional summary', dot: 'bg-critical' },
  { text: 'Quantify impact in bullet points', dot: 'bg-warning' },
  { text: 'Strong keyword coverage', dot: 'bg-success' },
]

const features = [
  {
    title: 'ATS check',
    description: 'Check if your resume passes tracking systems. Get a compatibility score and specific fixes.',
    icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2zM9 9h6v6H9V9z',
  },
  {
    title: 'Content review',
    description: 'Detailed feedback on your content, bullet points, action verbs, and impact statements.',
    icon: 'M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
  },
  {
    title: 'Formatting check',
    description: 'Review structure, consistency, section order, and visual presentation.',
    icon: 'M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5zM4 13a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6zM16 13a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-6z',
  },
  {
    title: 'Job comparison',
    description: 'Compare your resume against a job description for tailored, keyword-level suggestions.',
    icon: 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z',
  },
]

const steps = [
  { title: 'Upload your resume', description: 'Drag and drop your PDF or DOCX. We extract the text automatically.' },
  { title: 'Choose an analysis', description: 'Pick from ATS check, content review, formatting, or job comparison.' },
  { title: 'Get actionable feedback', description: 'A score, prioritised fixes, and specific edits you can make today.' },
]

const techStack = ['Laravel 13', 'Vue 3', 'TypeScript', 'Tailwind CSS', 'Queued jobs', 'Docker']
</script>

<style scoped>
@keyframes rise {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
