<template>
  <div dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-900">جدول المهندسين | Engineers</h1>
      <button
        type="button"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
        @click="openAdd"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        مهندس جديد
      </button>
    </div>

    <div class="mb-4 relative max-w-sm">
      <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="بحث بالاسم أو الهاتف أو البريد..."
        class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <div class="sf-card">
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="filteredEngineers.length === 0" class="p-12 text-center text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        لا يوجد مهندسون بعد
      </div>

      <div v-else class="sf-table-wrap"><table class="sf-table">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-3 text-right font-medium text-gray-500">الاسم</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">الهاتف</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">البريد</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">ملاحظات</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500">تواصل</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500">الإجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="e in filteredEngineers" :key="e.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs flex-shrink-0">
                  {{ e.name.charAt(0).toUpperCase() }}
                </div>
                <span class="font-medium text-gray-800">{{ e.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ e.phone || '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ e.email || '—' }}</td>
            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ e.notes || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-3">
                <a
                  v-if="whatsappLink(e.phone)"
                  :href="whatsappLink(e.phone, defaultMessage(e))!"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-green-600 hover:text-green-700"
                  title="واتساب"
                >
                  <svg class="w-5 h-5" viewBox="0 0 32 32" fill="currentColor"><path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/></svg>
                </a>
                <a
                  v-if="mailtoLink(e.email)"
                  :href="mailtoLink(e.email, 'SmartFlow')!"
                  class="text-blue-500 hover:text-blue-600"
                  title="إيميل"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </a>
                <span v-if="!whatsappLink(e.phone) && !mailtoLink(e.email)" class="text-gray-300 text-xs">—</span>
              </div>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <button @click="openEdit(e)" class="text-blue-500 hover:text-blue-700 transition-colors" title="تعديل">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="confirmDelete(e)" class="text-red-500 hover:text-red-700 transition-colors" title="حذف">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Add / edit modal -->
    <Teleport to="body">
      <div v-if="showModal" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-md">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">{{ editTarget ? 'تعديل مهندس' : 'مهندس جديد' }}</h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الاسم *</label>
              <input v-model="form.name" type="text" required class="sf-field"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
              <input v-model="form.phone" type="text" placeholder="05xxxxxxxx" class="sf-field"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
              <input v-model="form.email" type="email" class="sf-field"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات (التخصص مثلاً)</label>
              <textarea v-model="form.notes" rows="3" class="sf-field sf-field--textarea"></textarea>
            </div>

            <p v-if="formError" class="text-red-600 text-sm">{{ formError }}</p>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="showModal = false" class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                إلغاء
              </button>
              <button type="submit" :disabled="submitting" class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">
                {{ submitting ? 'جاري الحفظ...' : 'حفظ' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete confirm -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف المهندس "{{ deleteTarget.name }}"؟</p>
          <p class="text-gray-500 text-sm mb-6">لا يمكن التراجع عن هذا الإجراء.</p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null" class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">إلغاء</button>
            <button @click="deleteEngineer" :disabled="deleteLoading" class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">حذف</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/lib/api'
import { whatsappLink, mailtoLink } from '@/lib/contact'

interface Engineer {
  id: number
  name: string
  phone: string | null
  email: string | null
  notes: string | null
}

const engineers = ref<Engineer[]>([])
const loading = ref(true)
const search = ref('')

const filteredEngineers = computed(() => {
  if (!search.value.trim()) return engineers.value
  const q = search.value.trim().toLowerCase()
  return engineers.value.filter((e) =>
    e.name.toLowerCase().includes(q) ||
    (e.phone || '').toLowerCase().includes(q) ||
    (e.email || '').toLowerCase().includes(q)
  )
})

const defaultMessage = (e: Engineer) => `مرحباً ${e.name}، لديك موعد جديد. يرجى مراجعة لوحة التحكم.`

const fetchEngineers = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/engineers')
    engineers.value = res.data || []
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const showModal = ref(false)
const editTarget = ref<Engineer | null>(null)
const submitting = ref(false)
const formError = ref('')
const form = reactive({ name: '', phone: '', email: '', notes: '' })

const openAdd = () => {
  editTarget.value = null
  Object.assign(form, { name: '', phone: '', email: '', notes: '' })
  formError.value = ''
  showModal.value = true
}

const openEdit = (e: Engineer) => {
  editTarget.value = e
  Object.assign(form, { name: e.name, phone: e.phone || '', email: e.email || '', notes: e.notes || '' })
  formError.value = ''
  showModal.value = true
}

const submitForm = async () => {
  formError.value = ''
  submitting.value = true
  try {
    if (editTarget.value) {
      const res = await api.patch(`/admin/engineers/${editTarget.value.id}`, form)
      const idx = engineers.value.findIndex((e) => e.id === editTarget.value!.id)
      if (idx !== -1) engineers.value[idx] = res.data
    } else {
      const res = await api.post('/admin/engineers', form)
      engineers.value.push(res.data)
    }
    showModal.value = false
  } catch (err: any) {
    const errors = err.response?.data?.errors
    formError.value = err.response?.data?.message
      || (errors ? Object.values(errors).flat()[0] as string : null)
      || 'حدث خطأ، حاول مرة أخرى'
  } finally {
    submitting.value = false
  }
}

const deleteTarget = ref<Engineer | null>(null)
const deleteLoading = ref(false)

const confirmDelete = (e: Engineer) => { deleteTarget.value = e }

const deleteEngineer = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await api.delete(`/admin/engineers/${deleteTarget.value.id}`)
    engineers.value = engineers.value.filter((e) => e.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (err) {
    alert('تعذر حذف المهندس')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchEngineers)
</script>
