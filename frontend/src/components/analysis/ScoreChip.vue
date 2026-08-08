<template>
  <span
    class="tabular grid h-10 w-10 shrink-0 place-items-center rounded-lg text-sm font-semibold"
    :class="band.classes"
    :title="score === null ? 'No score available' : `Score ${score} out of 100`"
  >
    {{ score ?? '—' }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { scoreBand } from '@/services/score'
import type { Analysis } from '@/types'

const props = defineProps<{ analysis: Analysis }>()

// Never show a missing score as zero: those mean very different things.
const score = computed(() => props.analysis.ats_score ?? null)
const band = computed(() => scoreBand(score.value))
</script>
