<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-900 mb-1">Admin Login</h1>
        <p class="text-gray-600">تسجيل دخول المدير</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            البريد الإلكتروني | Email
          </label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            class="sf-field"
            placeholder="info@smartflow.ae"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            كلمة السر | Password
          </label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            class="sf-field"
            placeholder="••••••••"
          />
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-3 rounded-lg transition-colors"
        >
          {{ loading ? 'جاري التحميل...' : 'تسجيل الدخول | Login' }}
        </button>
      </form>

      <div class="mt-6 text-center text-sm text-gray-600">
        <p>البيانات الافتراضية | Default credentials:</p>
        <p class="font-mono mt-1">info@smartflow.ae / </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value,
    })

    if (response.data.success) {
      sessionStorage.setItem('adminLoggedIn', 'true')
      sessionStorage.setItem('adminEmail', email.value)
      if (response.data.token) {
        sessionStorage.setItem('authToken', response.data.token)
      }
      router.push('/admin/dashboard')
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || 'فشل تسجيل الدخول | Login failed'
  } finally {
    loading.value = false
  }
}
</script>
