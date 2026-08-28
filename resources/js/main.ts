import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { VueQueryPlugin } from '@tanstack/vue-query'
import axios from 'axios'
import App from './App.vue'
import router from './router'
import './../css/app.css'

// Setup axios interceptor to add auth token
axios.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('authToken') || sessionStorage.getItem('adminToken')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(VueQueryPlugin)

app.mount('#app')
