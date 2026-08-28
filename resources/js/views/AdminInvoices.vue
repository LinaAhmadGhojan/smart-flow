<template>
  <div dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-900">الفواتير | Invoices</h1>
      <router-link to="/admin/quotations" class="text-sm text-blue-600 hover:text-blue-800">
        إنشاء فاتورة من عرض سعر ←
      </router-link>
    </div>

    <div class="mb-4 relative max-w-sm">
      <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="بحث بالرقم أو العميل..."
        class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <div class="sf-card">
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
      <div v-else-if="filtered.length === 0" class="p-12 text-center text-gray-400">
        لا فواتير بعد — افتح عرض سعر واضغط «إنشاء فاتورة»
      </div>
      <div v-else class="sf-table-wrap"><table class="sf-table">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-3 text-right font-medium text-gray-500">الفاتورة</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">من العرض</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">العميل</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">المشروع</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">المبلغ</th>
            <th class="px-4 py-3 text-right font-medium text-gray-500">%</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500">الحالة</th>
            <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="inv in filtered" :key="inv.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs">{{ inv.number }}</td>
            <td class="px-4 py-3 font-mono text-xs text-gray-500">
              <router-link
                v-if="inv.quotation_id"
                :to="`/admin/quotations/${inv.quotation_id}`"
                class="text-blue-600 hover:underline"
              >
                {{ inv.quotation?.number || '#' + inv.quotation_id }}
              </router-link>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3">{{ inv.client_name }}</td>
            <td class="px-4 py-3 text-sm text-gray-600 max-w-[160px] truncate">
              {{ inv.project?.title_ar || inv.project?.title || '—' }}
            </td>
            <td class="px-4 py-3">{{ formatDate(inv.date) }}</td>
            <td class="px-4 py-3 font-medium">{{ money(inv.amount, inv.currency) }}</td>
            <td class="px-4 py-3">{{ inv.percent != null ? inv.percent + '%' : '—' }}</td>
            <td class="px-4 py-3 text-center">
              <select
                :value="inv.status"
                class="text-xs border border-gray-200 rounded-lg px-2 py-1"
                @change="updateStatus(inv, ($event.target as HTMLSelectElement).value)"
              >
                <option value="draft">مسودة</option>
                <option value="sent">مرسلة</option>
                <option value="paid">مدفوعة</option>
                <option value="cancelled">ملغاة</option>
              </select>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-2">
                <router-link :to="`/admin/invoices/${inv.id}`" class="text-blue-600 hover:text-blue-800" title="عرض">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </router-link>
                <button type="button" @click="downloadPdf(inv)" class="text-emerald-600 hover:text-emerald-800" title="PDF">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
                  </svg>
                </button>
                <button type="button" @click="confirmDelete(inv)" class="text-red-500 hover:text-red-700" title="حذف">
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
          <p class="text-lg font-bold mb-2">حذف الفاتورة؟</p>
          <p class="text-sm text-gray-500 mb-6">{{ deleteTarget.number }}</p>
          <div class="flex gap-3">
            <button type="button" @click="deleteTarget = null" class="flex-1 border py-2.5 rounded-lg text-sm">إلغاء</button>
            <button type="button" @click="doDelete" class="flex-1 bg-red-600 text-white py-2.5 rounded-lg text-sm">حذف</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/lib/api'

interface Invoice {
  id: number
  number: string
  quotation_id: number | null
  quotation?: { id: number; number: string; total: number }
  project?: { id: number; title: string; title_ar?: string } | null
  date: string
  client_name: string
  status: string
  currency: string
  amount: number
  percent: number | null
  total: number
}

const list = ref<Invoice[]>([])
const loading = ref(true)
const search = ref('')
const deleteTarget = ref<Invoice | null>(null)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return list.value
  return list.value.filter((x) =>
    x.number.toLowerCase().includes(q) ||
    x.client_name.toLowerCase().includes(q) ||
    (x.quotation?.number || '').toLowerCase().includes(q) ||
    (x.project?.title_ar || '').toLowerCase().includes(q) ||
    (x.project?.title || '').toLowerCase().includes(q)
  )
})

const money = (n: number, currency = 'AED') =>
  `${currency} ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const formatDate = (d: string) => {
  try { return new Date(d).toLocaleDateString('en-GB') } catch { return d }
}

const fetchList = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/invoices')
    list.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const updateStatus = async (inv: Invoice, status: string) => {
  try {
    await api.put(`/admin/invoices/${inv.id}`, { status })
    inv.status = status
  } catch {
    alert('تعذر تحديث الحالة')
  }
}

const confirmDelete = (inv: Invoice) => { deleteTarget.value = inv }

const doDelete = async () => {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/admin/invoices/${deleteTarget.value.id}`)
    list.value = list.value.filter((x) => x.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch {
    alert('تعذر الحذف')
  }
}

const downloadPdf = async (inv: Invoice) => {
  try {
    const res = await api.get(`/admin/invoices/${inv.id}/pdf`, {
      responseType: 'blob',
      headers: { Accept: 'application/pdf' },
    })
    const type = res.headers['content-type'] || ''
    if (type.includes('application/json')) {
      const text = await (res.data as Blob).text()
      const err = JSON.parse(text)
      throw new Error(err.message || 'PDF error')
    }
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${inv.number}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e: any) {
    alert(e?.message || 'تعذر تصدير PDF')
  }
}

onMounted(fetchList)
</script>
