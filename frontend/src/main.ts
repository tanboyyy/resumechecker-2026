import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { initTheme } from './composables/useTheme'
import './style.css'

// Before mount, so the first paint is already in the correct theme.
initTheme()

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
