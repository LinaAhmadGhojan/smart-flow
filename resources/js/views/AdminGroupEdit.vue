<template>
  <div class="w-full min-w-0">
    <div class="sf-page-header">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ isNew ? 'إضافة مجموعة | Add Group' : 'تعديل مجموعة | Edit Group' }}
      </h1>
      <RouterLink to="/admin/groups" class="text-sm text-gray-500 hover:text-blue-600">
        ← العودة للمجموعات
      </RouterLink>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="sf-card p-4 sm:p-6 md:p-8 space-y-6 w-full">
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

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="min-w-0 space-y-4 lg:col-span-2">
          <div>
            <label class="sf-label">Description (English)</label>
            <textarea v-model="form.description" rows="4" class="sf-field"></textarea>
          </div>
          <div>
            <label class="sf-label">الوصف (عربي)</label>
            <textarea v-model="form.description_ar" rows="4" class="sf-field" dir="rtl"></textarea>
          </div>
          <div class="max-w-xs">
            <label class="sf-label">Sort Order | الترتيب</label>
            <input v-model.number="form.sort_order" type="number" min="0" class="sf-field" />
          </div>
        </div>

        <div class="min-w-0">
          <label class="sf-label">Group Image | صورة المجموعة</label>
          <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4">
            <div v-if="imagePreview" class="mb-3">
              <img :src="imagePreview" alt="Group preview" class="h-40 w-full object-cover rounded-xl border" />
            </div>
            <div v-else class="mb-3 h-40 flex items-center justify-center text-sm text-gray-400 rounded-xl bg-white border border-dashed">
              لا توجد صورة
            </div>
            <input type="file" accept="image/*" class="sf-field" @change="handleImageUpload" />
          </div>
        </div>
      </div>

      <div class="sf-actions border-t pt-4">
        <RouterLink
          to="/admin/groups"
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
import { mediaUrl } from '@/lib/media'

const route = useRoute()
const router = useRouter()
const isNew = computed(() => !route.params.id)
const loading = ref(false)
const error = ref('')
const imageFile = ref<File | null>(null)
const imagePreview = ref('')

const form = ref({
  name: '',
  name_ar: '',
  description: '',
  description_ar: '',
  sort_order: 0,
})

const handleImageUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const fetchGroup = async () => {
  if (isNew.value) return
  try {
    const res = await api.get(`/groups/${route.params.id}`)
    form.value = {
      name: res.data.name,
      name_ar: res.data.name_ar,
      description: res.data.description || '',
      description_ar: res.data.description_ar || '',
      sort_order: res.data.sort_order || 0,
    }
    if (res.data.image) {
      imagePreview.value = mediaUrl(res.data.image)
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to load group'
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('name_ar', form.value.name_ar)
    formData.append('description', form.value.description)
    formData.append('description_ar', form.value.description_ar)
    formData.append('sort_order', String(form.value.sort_order || 0))
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    if (isNew.value) {
      await api.post('/groups', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      await api.post(`/groups/${route.params.id}?_method=PUT`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    router.push('/admin/groups')
  } catch (err: any) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to save group'
  } finally {
    loading.value = false
  }
}

onMounted(fetchGroup)
</script>
