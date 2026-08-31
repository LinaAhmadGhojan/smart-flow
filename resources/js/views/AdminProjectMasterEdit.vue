<template>
  <div class="w-full min-w-0" dir="rtl">
    <div class="sf-page-header">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ isNew ? 'إضافة مشروع للموقع' : 'تعديل مشروع الموقع' }}
      </h1>
      <RouterLink to="/admin/project-masters" class="text-sm text-gray-500 hover:text-blue-600">
        ← العودة لمشاريع الموقع
      </RouterLink>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="sf-card p-4 sm:p-6 md:p-8 space-y-6 w-full max-w-4xl">
      <div class="sf-form-grid">
        <div class="min-w-0">
          <label class="sf-label">Title (English)</label>
          <input v-model="form.title" type="text" required class="sf-field" />
        </div>
        <div class="min-w-0">
          <label class="sf-label">العنوان (عربي)</label>
          <input v-model="form.title_ar" type="text" required class="sf-field" dir="rtl" />
        </div>
      </div>

      <div class="sf-form-grid">
        <div class="min-w-0">
          <label class="sf-label">Description (English)</label>
          <textarea v-model="form.description" rows="4" class="sf-field"></textarea>
        </div>
        <div class="min-w-0">
          <label class="sf-label">الوصف (عربي)</label>
          <textarea v-model="form.description_ar" rows="4" class="sf-field" dir="rtl"></textarea>
        </div>
      </div>

      <div class="sf-form-grid">
        <div class="min-w-0">
          <label class="sf-label">الموقع | Location</label>
          <input v-model="form.location" type="text" class="sf-field" />
        </div>
        <div class="min-w-0">
          <label class="sf-label">ترتيب العرض</label>
          <input v-model.number="form.order" type="number" min="0" class="sf-field" />
        </div>
      </div>

      <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="form.is_visible" type="checkbox" class="w-4 h-4" />
          <span class="text-sm font-medium">عرض في الموقع الخارجي</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="form.is_featured" type="checkbox" class="w-4 h-4" />
          <span class="text-sm font-medium">مشروع مميز</span>
        </label>
      </div>

      <div>
        <label class="sf-label">صور / فيديو / ملفات المشروع</label>
        <p class="text-xs text-gray-500 mb-3">تظهر للزوار في بطاقة المشروع بالصفحة الرئيسية</p>

        <div v-if="existingFiles.length" class="space-y-2 mb-4">
          <div
            v-for="f in existingFiles"
            :key="f.id"
            class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-gray-50"
          >
            <img
              v-if="f.kind === 'image'"
              :src="mediaUrl(f.path)"
              class="h-12 w-16 object-cover rounded"
              @error="handleMediaError"
            />
            <span v-else class="text-xs px-2 py-1 rounded bg-white border">{{ f.kind }}</span>
            <span class="flex-1 text-sm truncate">{{ f.label }}</span>
            <button type="button" class="text-red-600 text-sm" @click="removeExisting(f.id)">إزالة</button>
          </div>
        </div>

        <div v-for="(row, idx) in pendingFiles" :key="'new-' + idx" class="flex flex-wrap items-center gap-2 mb-2">
          <input type="file" class="text-sm" @change="onFilePick(idx, $event)" />
          <input v-model="row.label" type="text" placeholder="تسمية الملف" class="sf-field flex-1 min-w-[140px]" />
          <button type="button" class="text-red-600 text-sm" @click="pendingFiles.splice(idx, 1)">×</button>
        </div>
        <button type="button" class="text-sm text-blue-600 hover:underline" @click="addPendingFile">+ إضافة ملف</button>
      </div>

      <div class="sf-actions border-t pt-4">
        <RouterLink
          to="/admin/project-masters"
          class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-center"
        >إلغاء</RouterLink>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg"
        >
          {{ loading ? 'جاري الحفظ...' : (isNew ? 'إنشاء' : 'تحديث') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface MasterFile {
  id: number
  label: string
  path: string
  kind: string
}

const route = useRoute()
const router = useRouter()
const isNew = computed(() => !route.params.id || route.params.id === 'new')
const loading = ref(false)
const error = ref('')

const form = ref({
  title: '',
  title_ar: '',
  description: '',
  description_ar: '',
  location: '',
  order: 0,
  is_visible: false,
  is_featured: false,
})

const allFiles = ref<MasterFile[]>([])
const keepFileIds = ref<number[]>([])
const pendingFiles = ref<{ file: File | null; label: string }[]>([])

const existingFiles = computed(() =>
  allFiles.value.filter((f) => keepFileIds.value.includes(f.id))
)

const addPendingFile = () => {
  pendingFiles.value.push({ file: null, label: '' })
}

const onFilePick = (idx: number, e: Event) => {
  const input = e.target as HTMLInputElement
  pendingFiles.value[idx].file = input.files?.[0] ?? null
}

const removeExisting = (id: number) => {
  keepFileIds.value = keepFileIds.value.filter((x) => x !== id)
}

const fetchItem = async () => {
  if (isNew.value) return
  try {
    const { data } = await api.get(`/admin/project-masters/${route.params.id}`)
    form.value = {
      title: data.title || '',
      title_ar: data.title_ar || '',
      description: data.description || '',
      description_ar: data.description_ar || '',
      location: data.location || '',
      order: data.order ?? 0,
      is_visible: !!data.is_visible,
      is_featured: !!data.is_featured,
    }
    allFiles.value = Array.isArray(data.files) ? data.files : []
    keepFileIds.value = allFiles.value.map((f) => f.id)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'تعذر تحميل المشروع'
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('title', form.value.title)
    fd.append('title_ar', form.value.title_ar)
    fd.append('description', form.value.description)
    fd.append('description_ar', form.value.description_ar)
    fd.append('location', form.value.location)
    fd.append('order', String(form.value.order ?? 0))
    fd.append('is_visible', form.value.is_visible ? '1' : '0')
    fd.append('is_featured', form.value.is_featured ? '1' : '0')
    fd.append('keep_file_ids', JSON.stringify(keepFileIds.value))

    pendingFiles.value.forEach((row, i) => {
      if (row.file) {
        fd.append('files[]', row.file)
        fd.append(`file_labels[${i}]`, row.label || row.file.name)
      }
    })

    if (isNew.value) {
      await api.post('/project-masters', fd)
    } else {
      await api.post(`/project-masters/${route.params.id}`, fd)
    }
    router.push('/admin/project-masters')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'تعذر الحفظ'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchItem()
  if (isNew.value) addPendingFile()
})
</script>
