import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { MotionPlugin } from '@vueuse/motion'
import { createHead } from '@unhead/vue/client'
import App from './App.vue'
import 'primeicons/primeicons.css'
import router from './router'
import { useAuthStore } from './stores/auth'

import './assets/main.css'

const app = createApp(App)
const pinia = createPinia()
const head = createHead()

app.use(pinia)
app.use(router)
app.use(MotionPlugin)
app.use(head)

const authStore = useAuthStore()
authStore.initAuth()

app.mount('#app')
