<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">طلبات الدراسة | Study Requests</h1>

    <div class="mb-4 flex flex-wrap gap-2">
      <button
        v-for="filter in kindFilters"
        :key="filter.value"
        type="button"
        class="px-4 py-2 rounded-lg text-sm font-medium border"
        :class="activeKind === filter.value ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
        @click="setKindFilter(filter.value)"
      >
        {{ filter.label }}
      </button>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
      <button
        v-for="filter in statusFilters"
        :key="filter.value"
        type="button"
        class="px-4 py-2 rounded-lg text-sm font-medium border"
        :class="activeStatus === filter.value ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
        @click="setStatusFilter(filter.value)"
      >
        {{ filter.label }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="visibleRequests.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-xl shadow">
      لا توجد طلبات حالياً
    </div>

    <div v-else class="space-y-4">
      <div v-for="req in visibleRequests" :key="req.key" class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 flex flex-wrap items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
              <span class="font-bold text-gray-900">{{ req.customer_name }}</span>
              <span
                class="text-xs font-bold px-2 py-0.5 rounded-full"
                :class="req.kind === 'gate' ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700'"
              >
                {{ req.kind === 'gate' ? 'ماكينة باب' : 'دراسة مشروع' }}
              </span>
              <span
                v-if="req.kind === 'project'"
                class="text-xs font-bold px-2 py-0.5 rounded-full"
                :class="req.house_status === 'existing' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700'"
              >
                {{ req.house_status === 'existing' ? 'منزل قائم' : 'قيد الإنشاء' }}
              </span>
            </div>
            <div class="text-sm text-gray-500">
              {{ req.customer_phone }}
              <span v-if="req.kind === 'gate' && req.site_location"> · {{ req.site_location }}</span>
              <span v-if="req.kind === 'project' && req.project_location"> · {{ req.project_location }}</span>
              · {{ formatDate(req.created_at) }}
              <span v-if="req.kind === 'project' && req.appointment_slot" class="text-blue-600">
                · موعد الزيارة: {{ req.appointment_slot.date }} {{ formatTime(req.appointment_slot.start_time) }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <select
              v-model="req.status"
              class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm"
              @change="updateStatus(req)"
            >
              <option value="new">جديد</option>
              <option value="contacted">تم التواصل</option>
              <option value="done">مكتمل</option>
            </select>
            <button type="button" class="text-blue-600 hover:text-blue-800 text-sm" @click="toggleExpand(req.key)">
              {{ expanded === req.key ? 'إخفاء' : 'التفاصيل' }}
            </button>
            <a
              v-if="whatsappLink(req.customer_phone, whatsappMessage(req))"
              :href="whatsappLink(req.customer_phone, whatsappMessage(req))!"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition-colors"
              title="واتساب العميل"
            >
              <svg class="w-5 h-5" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/></svg>
            </a>
            <button
              type="button"
              class="text-red-500 hover:text-red-700 transition-colors"
              title="حذف"
              @click="confirmDelete(req)"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Project study details -->
        <div v-if="expanded === req.key && req.kind === 'project'" class="border-t border-gray-100 p-5 bg-gray-50 text-sm space-y-3">
          <div v-if="req.systems?.length">
            <span class="font-medium text-gray-700">الأنظمة المطلوبة: </span>
            {{ req.systems.map(labelForSystem).join('، ') }}
            <span v-if="req.systems_other"> ({{ req.systems_other }})</span>
          </div>
          <div v-if="req.plans?.length">
            <span class="font-medium text-gray-700">المخططات: </span>
            {{ req.plans.map(labelForPlan).join('، ') }}
          </div>
          <div v-if="req.plan_files?.length">
            <span class="font-medium text-gray-700">ملفات مرفقة: </span>
            <a
              v-for="(file, idx) in req.plan_files"
              :key="idx"
              :href="file"
              target="_blank"
              rel="noopener noreferrer"
              class="text-blue-600 hover:underline me-2"
            >
              ملف {{ idx + 1 }}
            </a>
          </div>
          <div v-if="req.infrastructure_by">
            <span class="font-medium text-gray-700">تنفيذ الأعمال المدنية: </span>
            {{ req.infrastructure_by === 'contractor' ? 'بواسطة مقاول المشروع' : 'بواسطة الشركة' }}
          </div>
          <div v-if="req.proposed_system">
            <span class="font-medium text-gray-700">النظام المقترح: </span>
            {{ req.proposed_system === 'wired' ? 'نظام كيبل (KNX / TIS)' : 'نظام لاسلكي (Akubela ZIGBEE)' }}
          </div>
          <div v-if="req.project_location">
            <span class="font-medium text-gray-700">موقع المشروع: </span>
            {{ req.project_location }}
          </div>
          <div v-if="req.notes">
            <span class="font-medium text-gray-700">ملاحظات: </span>
            {{ req.notes }}
          </div>
        </div>

        <!-- Gate machine details -->
        <div v-if="expanded === req.key && req.kind === 'gate'" class="border-t border-gray-100 p-5 bg-gray-50 text-sm space-y-2">
          <div v-if="req.site_location"><span class="font-medium text-gray-700">الموقع: </span>{{ req.site_location }}</div>
          <div v-if="req.door_weight"><span class="font-medium text-gray-700">وزن الباب: </span>{{ req.door_weight }} كغ</div>
          <div v-if="req.door_width || req.door_height">
            <span class="font-medium text-gray-700">أبعاد الباب: </span>
            {{ req.door_width || '—' }} × {{ req.door_height || '—' }} سم
          </div>
          <div v-if="req.door_material"><span class="font-medium text-gray-700">نوع الباب: </span>{{ req.door_material }}</div>
          <div><span class="font-medium text-gray-700">نقطة كهربائية: </span>{{ labelYesNo(req.has_electrical_point) }}</div>
          <div><span class="font-medium text-gray-700">تمديدات ماكينة الباب: </span>{{ labelYesNo(req.has_machine_wiring) }}</div>
          <div v-if="req.notes"><span class="font-medium text-gray-700">ملاحظات: </span>{{ req.notes }}</div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف الطلب؟</p>
          <p class="text-gray-500 text-sm mb-6">{{ deleteTarget.customer_name }} — {{ deleteTarget.customer_phone }}</p>
          <div class="flex gap-3">
            <button type="button" @click="deleteTarget = null" class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">إلغاء</button>
            <button type="button" @click="doDelete" :disabled="deleteLoading" class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-medium">حذف</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/lib/api'
import { whatsappLink } from '@/lib/contact'

type RequestKind = 'project' | 'gate'
type RequestStatus = 'new' | 'contacted' | 'done'

interface ProjectStudyRequest {
  kind: 'project'
  key: string
  id: number
  house_status: 'under_construction' | 'existing'
  systems: string[]
  systems_other: string | null
  plans: string[]
  plan_files: string[]
  infrastructure_by: 'contractor' | 'company' | null
  proposed_system: 'wired' | 'wireless' | null
  customer_name: string
  customer_phone: string
  project_location: string | null
  notes: string | null
  status: RequestStatus
  created_at: string
  appointment_slot?: { id: number; date: string; start_time: string } | null
}

interface GateStudyRequest {
  kind: 'gate'
  key: string
  id: number
  customer_name: string
  customer_phone: string
  site_location: string
  door_weight: string | null
  door_width: string | null
  door_height: string | null
  door_material: string | null
  has_electrical_point: 'yes' | 'no' | 'unknown'
  has_machine_wiring: 'yes' | 'no' | 'unknown'
  notes: string | null
  status: RequestStatus
  created_at: string
}

type UnifiedRequest = ProjectStudyRequest | GateStudyRequest

const systemLabels: Record<string, string> = {
  cameras: 'نظام الكاميرات',
  internet: 'توزيع الإنترنت',
  intercom: 'نظام الإنتركم',
  lighting: 'التحكم بالإضاءة',
  ac: 'التحكم بالمكيف',
  curtains: 'التحكم بالستائر',
  garage: 'ماكينة/بوابة الكراج',
  audio: 'نظام الصوت',
  other: 'أخرى',
}

const planLabels: Record<string, string> = {
  architectural: 'المخطط المعماري',
  electrical: 'مخطط الكهرباء',
  ac: 'مخطط التكييف',
  communications: 'مخطط الاتصالات',
}

const kindFilters = [
  { value: '', label: 'كل الطلبات' },
  { value: 'project', label: 'دراسة مشروع' },
  { value: 'gate', label: 'ماكينة باب' },
]

const statusFilters = [
  { value: '', label: 'كل الحالات' },
  { value: 'new', label: 'جديد' },
  { value: 'contacted', label: 'تم التواصل' },
  { value: 'done', label: 'مكتمل' },
]

const requests = ref<UnifiedRequest[]>([])
const loading = ref(true)
const activeKind = ref('')
const activeStatus = ref('')
const expanded = ref<string | null>(null)
const deleteTarget = ref<UnifiedRequest | null>(null)
const deleteLoading = ref(false)

const visibleRequests = computed(() => {
  if (!activeKind.value) return requests.value
  return requests.value.filter((r) => r.kind === activeKind.value)
})

const labelForSystem = (value: string) => systemLabels[value] || value
const labelForPlan = (value: string) => planLabels[value] || value
const labelYesNo = (v?: string) => ({ yes: 'نعم', no: 'لا', unknown: 'لا أعلم' }[v || ''] || v || '—')

const formatDate = (dateStr: string) =>
  new Date(dateStr).toLocaleDateString('ar-EG', { day: 'numeric', month: 'long', year: 'numeric' })

const formatTime = (time: string) => {
  const [h, m] = time.split(':').map(Number)
  const period = h >= 12 ? 'م' : 'ص'
  const hour12 = h % 12 === 0 ? 12 : h % 12
  return `${hour12}:${String(m).padStart(2, '0')} ${period}`
}

const whatsappMessage = (req: UnifiedRequest) => {
  if (req.kind === 'gate') {
    return [
      `مرحباً ${req.customer_name}،`,
      'بخصوص طلب دراسة ماكينة الباب في SmartFlow.',
      req.site_location ? `الموقع: ${req.site_location}` : '',
      req.door_weight ? `وزن الباب: ${req.door_weight} كغ` : '',
      req.door_width || req.door_height ? `أبعاد الباب: ${req.door_width || '—'} × ${req.door_height || '—'} سم` : '',
    ].filter(Boolean).join('\n')
  }

  return [
    `مرحباً ${req.customer_name}،`,
    'بخصوص طلب دراسة المشروع في SmartFlow.',
    req.project_location ? `موقع المشروع: ${req.project_location}` : '',
    req.appointment_slot
      ? `موعد الزيارة: ${req.appointment_slot.date} ${formatTime(req.appointment_slot.start_time)}`
      : '',
  ].filter(Boolean).join('\n')
}

const mapProject = (row: Omit<ProjectStudyRequest, 'kind' | 'key'>): ProjectStudyRequest => ({
  ...row,
  kind: 'project',
  key: `project-${row.id}`,
})

const mapGate = (row: Omit<GateStudyRequest, 'kind' | 'key'>): GateStudyRequest => ({
  ...row,
  kind: 'gate',
  key: `gate-${row.id}`,
})

const fetchRequests = async () => {
  loading.value = true
  const params = activeStatus.value ? { status: activeStatus.value } : {}
  try {
    const [projectRes, gateRes] = await Promise.all([
      api.get('/admin/study-requests', { params }),
      api.get('/admin/gate-machine-studies', { params }),
    ])
    const projectRows = (Array.isArray(projectRes.data) ? projectRes.data : []).map(mapProject)
    const gateRows = (Array.isArray(gateRes.data) ? gateRes.data : []).map(mapGate)
    requests.value = [...projectRows, ...gateRows].sort(
      (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    )
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const setKindFilter = (value: string) => {
  activeKind.value = value
}

const setStatusFilter = (value: string) => {
  activeStatus.value = value
  fetchRequests()
}

const toggleExpand = (key: string) => {
  expanded.value = expanded.value === key ? null : key
}

const updateStatus = async (req: UnifiedRequest) => {
  const url =
    req.kind === 'gate'
      ? `/admin/gate-machine-studies/${req.id}`
      : `/admin/study-requests/${req.id}`
  try {
    await api.patch(url, { status: req.status })
  } catch {
    alert('تعذر تحديث الحالة')
  }
}

const confirmDelete = (req: UnifiedRequest) => {
  deleteTarget.value = req
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  const req = deleteTarget.value
  const url =
    req.kind === 'gate'
      ? `/admin/gate-machine-studies/${req.id}`
      : `/admin/study-requests/${req.id}`
  try {
    await api.delete(url)
    requests.value = requests.value.filter((r) => r.key !== req.key)
    if (expanded.value === req.key) expanded.value = null
    deleteTarget.value = null
  } catch {
    alert('تعذر حذف الطلب')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchRequests)
</script>
