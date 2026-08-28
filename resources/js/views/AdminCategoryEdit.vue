<template>
  <div class="w-full min-w-0">
    <div class="sf-page-header">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ isNew ? 'إضافة فئة | Add Category' : 'تعديل فئة | Edit Category' }}
      </h1>
      <RouterLink to="/admin/categories" class="text-sm text-gray-500 hover:text-blue-600">
        ← العودة للفئات
      </RouterLink>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="sf-card p-4 sm:p-6 md:p-8 space-y-6 w-full max-w-4xl">
      <div class="sf-form-grid">
        <div class="min-w-0">
          <label class="sf-label">Name (English)</label>
          <input v-model="form.name" type="text" required class="sf-field" />
        </div>
        <div class="min-w-0">
          <label class="sf-label">الاسم (عربي)</label>
          <input v-model="form.name_ar" type="text" required class="sf-field" dir="rtl" />
        </div>
      </div>

      <div>
        <label class="sf-label">Description | الوصف</label>
        <textarea v-model="form.description" rows="4" class="sf-field"></textarea>
      </div>

      <div class="sf-actions border-t pt-4">
        <RouterLink
          to="/admin/categories"
          class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-center"
        >
          Cancel | إلغاء
        </RouterLink>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg"
        >
          {{ loading ? 'Saving...' : (isNew ? 'Create | إنشاء' : 'Update | تحديث') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/lib/api'

const route = useRoute()
const router = useRouter()
const isNew = computed(() => !route.params.id)
const loading = ref(false)
const error = ref('')

const form = ref({
  name: '',
  name_ar: '',
  description: '',
})

const fetchCategory = async () => {
  if (isNew.value) return
  try {
    const response = await api.get(`/categories/${route.params.id}`)
    form.value = {
      name: response.data.name,
      name_ar: response.data.name_ar,
      description: response.data.description || '',
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to load category'
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    if (isNew.value) {
      await api.post('/categories', form.value)
    } else {
      await api.put(`/categories/${route.params.id}`, form.value)
    }
    router.push('/admin/categories')
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to save category'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!isNew.value) fetchCategory()
})
</script>
