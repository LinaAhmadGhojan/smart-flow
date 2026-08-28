<template>
  <div dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-900">تقارير الزيارات | Reports</h1>
      <router-link
        to="/admin/reports/new"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        تقرير جديد
      </router-link>
    </div>

    <div class="mb-4 relative max-w-sm">
      <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="بحث باسم العميل أو المهندس أو العنوان..."
        class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <div v-if="loading" class="text-center py-16">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="filteredReports.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
      <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      لا يوجد تقارير بعد
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div
        v-for="r in filteredReports"
        :key="r.id"
        class="bg-white rounded-xl shadow-sm overflow-hidden group relative"
      >
        <!-- Square cover image (Instagram-style) -->
        <div class="relative aspect-square bg-gray-100">
          <img
            v-if="r.images && r.images.length"
            :src="mediaUrl(r.images[0])"
            class="w-full h-full object-cover"
            @error="handleMediaError"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 20h16a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <span
            v-if="r.images && r.images.length > 1"
            class="absolute top-2 left-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded-full"
          >
            +{{ r.images.length - 1 }}
          </span>

          <!-- Hover actions -->
          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
            <router-link :to="`/admin/reports/${r.id}`" title="تعديل" class="w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center text-blue-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </router-link>
            <router-link :to="`/admin/reports/${r.id}/view`" title="معاينة HTML" class="w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center text-indigo-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </router-link>
            <button @click="downloadPdf(r)" title="تصدير PDF" class="w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center text-emerald-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
              </svg>
            </button>
            <button @click="confirmDelete(r)" title="حذف" class="w-9 h-9 rounded-full bg-white/90 hover:bg-white flex items-center justify-center text-red-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="p-3">
          <p class="font-bold text-gray-900 text-sm truncate">{{ r.title || 'تقرير زيارة' }}</p>
          <p class="text-xs text-gray-500 truncate mt-0.5">{{ r.client_name || 'بدون اسم عميل' }}</p>
          <p class="text-[11px] text-gray-400 mt-1">{{ formatDate(r.visit_date) }}</p>
        </div>
      </div>
    </div>

    <!-- Delete confirm -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" dir="rtl">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف هذا التقرير؟</p>
          <p class="text-gray-500 text-sm mb-6">لا يمكن التراجع عن هذا الإجراء.</p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null" class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">إلغاء</button>
            <button @click="deleteReport" :disabled="deleteLoading" class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">حذف</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface Report {
  id: number
  appointment_slot_id: number | null
  title: string | null
  content: string | null
  images: string[] | null
  client_name: string | null
  engineer_name: string | null
  visit_date: string | null
  created_at: string
}

const reports = ref<Report[]>([])
const loading = ref(true)
const search = ref('')

const filteredReports = computed(() => {
  if (!search.value.trim()) return reports.value
  const q = search.value.trim().toLowerCase()
  return reports.value.filter((r) =>
    (r.client_name || '').toLowerCase().includes(q) ||
    (r.engineer_name || '').toLowerCase().includes(q) ||
    (r.title || '').toLowerCase().includes(q)
  )
})

const formatDate = (d: string | null) => {
  if (!d) return '—'
  try {
    return new Date(d).toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' })
  } catch {
    return d
  }
}

const fetchReports = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/reports')
    reports.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

// --- delete ---
const deleteTarget = ref<Report | null>(null)
const deleteLoading = ref(false)

const confirmDelete = (r: Report) => { deleteTarget.value = r }

const deleteReport = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await api.delete(`/admin/reports/${deleteTarget.value.id}`)
    reports.value = reports.value.filter((r) => r.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (err) {
    alert('تعذر حذف التقرير')
  } finally {
    deleteLoading.value = false
  }
}

// --- PDF export ---
const downloadPdf = async (r: Report) => {
  try {
    const res = await api.get(`/admin/reports/${r.id}/pdf`, {
      responseType: 'blob',
      headers: { Accept: 'application/pdf' },
    })

    const contentType = String(res.headers['content-type'] || '')
    if (contentType.includes('application/json') || contentType.includes('text/html')) {
      const text = await (res.data as Blob).text()
      try {
        const json = JSON.parse(text)
        alert(json.message || 'تعذر تصدير التقرير كـ PDF')
      } catch {
        alert('تعذر تصدير التقرير كـ PDF')
      }
      return
    }

    const blob = new Blob([res.data], { type: 'application/pdf' })
    const blobUrl = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = blobUrl
    a.download = `report-${r.id}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(blobUrl)
  } catch (err: any) {
    const blob = err.response?.data
    if (blob instanceof Blob) {
      try {
        const json = JSON.parse(await blob.text())
        alert(json.message || 'تعذر تصدير التقرير كـ PDF')
        return
      } catch {
        // fall through
      }
    }
    alert(err.response?.data?.message || 'تعذر تصدير التقرير كـ PDF')
  }
}

onMounted(fetchReports)
</script>
