import { ref, computed } from 'vue'

export type ThemePreference = 'light' | 'dark' | 'system'

const STORAGE_KEY = 'resumeai:theme'

const media = window.matchMedia('(prefers-color-scheme: dark)')

function storedPreference(): ThemePreference {
  const saved = localStorage.getItem(STORAGE_KEY)
  return saved === 'light' || saved === 'dark' ? saved : 'system'
}

const preference = ref<ThemePreference>(storedPreference())

/** The theme actually in effect, once "system" is resolved. */
const resolved = computed<'light' | 'dark'>(() =>
  preference.value === 'system' ? (media.matches ? 'dark' : 'light') : preference.value
)

function apply() {
  document.documentElement.setAttribute('data-theme', resolved.value)
  document.documentElement.style.colorScheme = resolved.value
}

/**
 * Called from main.ts before the app mounts so the first paint is already in
 * the right theme.
 */
export function initTheme() {
  apply()
  // Follow the OS only while the user has not made an explicit choice.
  media.addEventListener('change', () => {
    if (preference.value === 'system') apply()
  })
}

export function useTheme() {
  function setPreference(next: ThemePreference) {
    preference.value = next

    if (next === 'system') {
      localStorage.removeItem(STORAGE_KEY)
    } else {
      localStorage.setItem(STORAGE_KEY, next)
    }

    apply()
  }

  function toggle() {
    setPreference(resolved.value === 'dark' ? 'light' : 'dark')
  }

  return { preference, resolved, setPreference, toggle }
}
