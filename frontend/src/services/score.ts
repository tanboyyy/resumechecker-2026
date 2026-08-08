/**
 * Score presentation, shared by the gauge, the chip, and the history list so a
 * score never reads one way in one place and another way elsewhere.
 *
 * A null score is "not available", which is deliberately not the same as zero.
 */
export interface ScoreBand {
  label: string
  classes: string
  /** For the gauge arc, which needs a raw colour rather than a utility class. */
  color: string
}

export function scoreBand(score: number | null): ScoreBand {
  if (score === null) {
    return {
      label: 'Not available',
      classes: 'bg-surface-muted text-content-subtle',
      color: 'var(--content-subtle)',
    }
  }

  if (score >= 80) {
    return { label: 'Strong', classes: 'bg-success-soft text-success', color: 'var(--success)' }
  }

  if (score >= 60) {
    return { label: 'Decent', classes: 'bg-info-soft text-info', color: 'var(--info)' }
  }

  if (score >= 40) {
    return { label: 'Needs work', classes: 'bg-warning-soft text-warning', color: 'var(--warning)' }
  }

  return { label: 'Weak', classes: 'bg-critical-soft text-critical', color: 'var(--critical)' }
}
