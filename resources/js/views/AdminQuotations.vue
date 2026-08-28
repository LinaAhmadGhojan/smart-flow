<template>
  <div dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-900">عروض الأسعار | Quotations</h1>
      <router-link
        to="/admin/quotations/new"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        عرض سعر جديد
      </router-link>
    </div>

    <div class="mb-4 relative max-w-sm">
      <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="بحث بالرقم أو العميل أو المشروع (عربي / English)..."
        class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <div class="sf-card">
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
      <div v-else-if="filtered.length === 0" class="p-12 text-center text-gray-400">
        لا يوجد عروض أسعار بعد
      </div>
      <div v-else class="sf-table-wrap"><table class="sf-table">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-3 text-right font-medium text-gray-500">الرقم</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">العميل</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">المشروع</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">الإجمالي</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">المفوتر</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">المتبقي</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500">الحالة</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="q in filtered" :key="q.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs text-gray-800">{{ q.number }}</td>
            <td class="px-4 py-3 font-medium text-gray-800">{{ q.client_name }}</td>
            <td class="px-4 py-3 text-sm text-gray-600 max-w-[160px] truncate">
              {{ q.project?.title_ar || q.project?.title || '—' }}
            </td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(q.date) }}</td>
            <td class="px-4 py-3 text-gray-800">{{ money(q.total, q.currency) }}</td>
            <td class="px-4 py-3 text-emerald-700">{{ money(q.invoiced_amount, q.currency) }}</td>
            <td class="px-4 py-3 text-amber-700 font-medium">{{ money(q.remaining_amount, q.currency) }}</td>
            <td class="px-4 py-3 text-center">
              <span class="text-xs px-2 py-0.5 rounded-full" :class="statusClass(q.status)">{{ statusLabel(q.status) }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-2">
                <router-link :to="`/admin/quotations/${q.id}`" class="text-blue-500 hover:text-blue-700" title="تعديل">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </router-link>
                <button type="button" @click="downloadPdf(q)" class="text-emerald-600 hover:text-emerald-800" title="PDF">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
                  </svg>
                </button>
                <button type="button" @click="confirmDelete(q)" class="text-red-500 hover:text-red-700" title="حذف">
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

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <p class="text-lg font-bold text-gray-900 mb-2">حذف عرض السعر؟</p>
          <p class="text-gray-500 text-sm mb-6">{{ deleteTarget.number }} — {{ deleteTarget.client_name }}</p>
          <div class="flex gap-3">
            <button type="button" @click="deleteTarget = null" class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm">إلغاء</button>
            <button type="button" @click="doDelete" :disabled="deleteLoading" class="flex-1 bg-red-600 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60">حذف</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/lib/api'

interface Quotation {
  id: number
  number: string
  date: string
  client_name: string
  status: string
  currency: string
  total: number
  invoiced_amount: number
  remaining_amount: number
  project?: { id: number; title: string; title_ar?: string } | null
}

const list = ref<Quotation[]>([])
const loading = ref(true)
const search = ref('')
const deleteTarget = ref<Quotation | null>(null)
const deleteLoading = ref(false)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return list.value
  return list.value.filter((x) =>
    x.number.toLowerCase().includes(q) ||
    x.client_name.toLowerCase().includes(q) ||
    (x.project?.title_ar || '').toLowerCase().includes(q) ||
    (x.project?.title || '').toLowerCase().includes(q)
  )
})

const money = (n: number | string | null | undefined, currency = 'AED') =>
  `${currency} ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const formatDate = (d: string) => {
  try {
    return new Date(d).toLocaleDateString('en-GB')
  } catch {
    return d
  }
}

const statusLabel = (s: string) =>
  ({ draft: 'مسودة', sent: 'مرسل', accepted: 'مقبول', cancelled: 'ملغى' } as Record<string, string>)[s] || s

const statusClass = (s: string) =>
  ({
    draft: 'bg-gray-100 text-gray-700',
    sent: 'bg-blue-100 text-blue-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-red-100 text-red-700',
  } as Record<string, string>)[s] || 'bg-gray-100 text-gray-600'

const fetchList = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/quotations')
    list.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const confirmDelete = (q: Quotation) => { deleteTarget.value = q }

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await api.delete(`/admin/quotations/${deleteTarget.value.id}`)
    list.value = list.value.filter((x) => x.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch {
    alert('تعذر الحذف')
  } finally {
    deleteLoading.value = false
  }
}

const downloadPdf = async (q: Quotation) => {
  try {
    const res = await api.get(`/admin/quotations/${q.id}/pdf`, {
      responseType: 'blob',
      headers: { Accept: 'application/pdf' },
    })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${q.number}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch {
    alert('تعذر تصدير PDF')
  }
}

onMounted(fetchList)
</script>
