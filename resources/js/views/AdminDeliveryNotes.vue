<template>
  <div dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Delivery Notes</h1>
        <p class="text-sm text-gray-500 mt-0.5">إشعارات التسليم · جميع المشاريع</p>
      </div>
      <router-link to="/admin/projects" class="text-sm text-blue-600 hover:text-blue-800">
        إنشاء من صفحة المشروع ←
      </router-link>
    </div>

    <div class="mb-4 relative max-w-sm">
      <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="بحث بالرقم أو المشروع أو العميل..."
        class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <div class="sf-card">
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
      <div v-else-if="filtered.length === 0" class="p-12 text-center text-gray-400">
        لا إشعارات تسليم بعد — افتح مشروعاً وأنشئ Delivery Note
      </div>
      <div v-else class="sf-table-wrap">
        <table class="sf-table">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الرقم</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">العنوان</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">المشروع</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">العميل</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">البنود</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">استلم / سلّم</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="dn in filtered" :key="dn.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs font-semibold text-blue-800">{{ dn.number }}</td>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900">{{ dn.title || 'Delivery Note' }}</p>
                <p v-if="dn.notes" class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ dn.notes }}</p>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 max-w-[180px]">
                <router-link
                  v-if="dn.project_id"
                  :to="`/admin/projects/${dn.project_id}`"
                  class="text-blue-600 hover:underline line-clamp-1"
                >
                  {{ dn.project?.title_ar || dn.project?.title || '#' + dn.project_id }}
                </router-link>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-3 text-sm">{{ dn.project?.customer?.name || '—' }}</td>
              <td class="px-4 py-3">{{ formatDate(dn.delivered_at) }}</td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex min-w-[1.75rem] justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium">
                  {{ (dn.items || []).length }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-600">
                <div>{{ dn.received_by || '—' }}</div>
                <div class="text-gray-400">{{ dn.delivered_by || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                  <button type="button" class="text-blue-600 hover:text-blue-800" title="عرض" @click="openView(dn)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <button
                    type="button"
                    class="text-emerald-600 hover:text-emerald-800 disabled:opacity-40"
                    title="PDF"
                    :disabled="pdfExportId === dn.id"
                    @click="downloadPdf(dn)"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
                    </svg>
                  </button>
                  <button
                    v-if="dn.project?.status !== 'completed'"
                    type="button"
                    class="text-red-500 hover:text-red-700"
                    title="حذف"
                    @click="confirmDelete(dn)"
                  >
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

    <!-- View modal -->
    <Teleport to="body">
      <div v-if="viewTarget" class="sf-modal-backdrop" dir="rtl" @click.self="closeView">
        <div class="sf-modal-panel !max-w-5xl max-h-[95vh] overflow-hidden flex flex-col p-0">
          <div class="px-5 py-4 border-b flex items-start justify-between gap-3">
            <div>
              <p class="text-xs text-slate-400 font-mono">{{ viewTarget.number }}</p>
              <h3 class="text-lg font-bold text-slate-900">{{ viewTarget.title || 'دليفري نوت' }}</h3>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeView">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="flex-1 overflow-auto bg-slate-100 min-h-[60vh]">
            <div v-if="viewLoading" class="p-16 text-center">
              <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
            </div>
            <iframe
              v-else-if="viewHtml"
              ref="viewFrame"
              :srcdoc="viewHtml"
              class="w-full min-h-[80vh] border-0"
              title="دليفري نوت"
              @load="viewFrameReady = true"
            />
          </div>
          <div class="px-5 py-3 border-t flex gap-2 bg-white">
            <button type="button" class="flex-1 border py-2.5 rounded-lg text-sm" @click="closeView">إغلاق</button>
            <button
              type="button"
              class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg text-sm disabled:opacity-60"
              :disabled="pdfLoading || !viewFrameReady"
              @click="downloadPdf(viewTarget)"
            >
              {{ pdfLoading ? 'جاري التصدير...' : 'تحميل PDF' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <p class="text-lg font-bold mb-2">حذف Delivery Note؟</p>
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
import api, { fetchAdminHtml } from '@/lib/api'
import { exportDeliveryNotePdf, deliveryNoteHtmlPath } from '@/lib/receiptPdf'

interface DnItem {
  description: string
  quantity?: number
  unit?: string | null
}

interface DeliveryNote {
  id: number
  project_id: number
  number: string
  title?: string
  notes?: string | null
  delivered_at: string
  received_by?: string | null
  delivered_by?: string | null
  items?: DnItem[]
  project?: {
    id: number
    title: string
    title_ar?: string
    status?: string
    customer?: { id: number; name: string; phone?: string; email?: string } | null
  } | null
}

const list = ref<DeliveryNote[]>([])
const loading = ref(true)
const search = ref('')
const deleteTarget = ref<DeliveryNote | null>(null)
const viewTarget = ref<DeliveryNote | null>(null)
const viewHtml = ref('')
const viewFrame = ref<HTMLIFrameElement | null>(null)
const viewLoading = ref(false)
const viewFrameReady = ref(false)
const pdfLoading = ref(false)
const pdfExportId = ref<number | null>(null)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return list.value
  return list.value.filter((x) =>
    (x.number || '').toLowerCase().includes(q) ||
    (x.title || '').toLowerCase().includes(q) ||
    (x.project?.title_ar || '').toLowerCase().includes(q) ||
    (x.project?.title || '').toLowerCase().includes(q) ||
    (x.project?.customer?.name || '').toLowerCase().includes(q) ||
    (x.received_by || '').toLowerCase().includes(q) ||
    (x.delivered_by || '').toLowerCase().includes(q)
  )
})

const formatDate = (d: string) => {
  try { return new Date(d).toLocaleDateString('en-GB') } catch { return d }
}

const fetchList = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/delivery-notes')
    list.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const openView = async (dn: DeliveryNote) => {
  viewTarget.value = dn
  viewHtml.value = ''
  viewFrameReady.value = false
  viewLoading.value = true
  try {
    viewHtml.value = await fetchAdminHtml(deliveryNoteHtmlPath(dn.project_id, dn.id))
  } catch {
    viewHtml.value = ''
    alert('تعذر تحميل نموذج الدليفري نوت')
    viewTarget.value = null
  } finally {
    viewLoading.value = false
  }
}

const closeView = () => {
  viewTarget.value = null
  viewHtml.value = ''
  viewFrameReady.value = false
}
const confirmDelete = (dn: DeliveryNote) => { deleteTarget.value = dn }

const doDelete = async () => {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/admin/projects/${deleteTarget.value.project_id}/delivery-notes/${deleteTarget.value.id}`)
    list.value = list.value.filter((x) => x.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر الحذف')
  }
}

const downloadPdf = async (dn: DeliveryNote) => {
  pdfExportId.value = dn.id
  pdfLoading.value = true
  try {
    const useFrame = viewTarget.value?.id === dn.id ? viewFrame.value : null
    await exportDeliveryNotePdf(dn.project_id, dn.id, dn.number, useFrame)
  } catch (e: any) {
    alert(e.message || e.response?.data?.message || 'تعذر تحميل PDF')
  } finally {
    pdfExportId.value = null
    pdfLoading.value = false
  }
}

onMounted(fetchList)
</script>
