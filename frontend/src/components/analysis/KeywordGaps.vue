<template>
  <section
    v-if="matched.length || missing.length"
    class="rounded-xl border border-border bg-surface p-5 shadow-card"
  >
    <h2 class="font-semibold text-content">Keywords</h2>
    <p class="mt-1 text-sm text-content-muted">
      How your resume lines up with the language in the job description.
    </p>

    <div class="mt-4 grid gap-5 sm:grid-cols-2">
      <div v-if="missing.length">
        <p class="flex items-baseline gap-2 text-sm font-medium text-content">
          Missing
          <span class="tabular text-xs font-normal text-content-subtle">{{ missing.length }}</span>
        </p>
        <p class="mt-0.5 text-xs text-content-subtle">Worth adding if they genuinely apply to you.</p>
        <ul class="mt-2.5 flex flex-wrap gap-1.5">
          <li
            v-for="keyword in missing"
            :key="keyword"
            class="rounded-md border border-critical-border bg-critical-soft px-2 py-1 text-xs font-medium text-critical"
          >
            {{ keyword }}
          </li>
        </ul>
      </div>

      <div v-if="matched.length">
        <p class="flex items-baseline gap-2 text-sm font-medium text-content">
          Already covered
          <span class="tabular text-xs font-normal text-content-subtle">{{ matched.length }}</span>
        </p>
        <p class="mt-0.5 text-xs text-content-subtle">These appear in both.</p>
        <ul class="mt-2.5 flex flex-wrap gap-1.5">
          <li
            v-for="keyword in matched"
            :key="keyword"
            class="rounded-md border border-success-border bg-success-soft px-2 py-1 text-xs font-medium text-success"
          >
            {{ keyword }}
          </li>
        </ul>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
defineProps<{ matched: string[]; missing: string[] }>()
</script>
