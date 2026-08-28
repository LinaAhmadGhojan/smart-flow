<template>
  <div dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">دفعات العملاء</h1>
        <p class="text-sm text-gray-500 mt-0.5">تسجيل ومتابعة الدفعات — مطلوب مشروع أو فاتورة</p>
      </div>
      <button
        type="button"
        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium"
        @click="openCreate"
      >
        + دفعة جديدة
      </button>
    </div>

    <div class="mb-4 relative max-w-sm">
      <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="بحث بالمشروع أو العميل أو المبلغ..."
        class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-amber-400"
      />
    </div>

    <div class="sf-card">
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-amber-500"></div>
      </div>
      <div v-else-if="filtered.length === 0" class="p-12 text-center text-gray-400">
        لا دفعات بعد — اضغط «دفعة جديدة» واربطها بمشروع
      </div>
      <div v-else class="sf-table-wrap">
        <table class="sf-table">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">المبلغ</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">النوع</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">المشروع</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الفاتورة</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">العميل</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الوصل</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">ملاحظة</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in filtered" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-700">{{ formatDate(p.paid_at) }}</td>
              <td class="px-4 py-3 font-semibold text-amber-800">{{ money(p.amount) }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full bg-amber-50 text-amber-800 px-2.5 py-0.5 text-xs font-medium">
                  {{ paymentTypeLabel(p.payment_type) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm max-w-[180px]">
                <router-link
                  v-if="p.project_id"
                  :to="`/admin/projects/${p.project_id}`"
                  class="text-blue-600 hover:underline line-clamp-1"
                >
                  {{ p.project?.title_ar || p.project?.title || '#' + p.project_id }}
                </router-link>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-3 text-sm">
                <router-link
                  v-if="p.invoice_id"
                  :to="`/admin/invoices/${p.invoice_id}`"
                  class="text-blue-600 hover:underline font-mono text-xs"
                >
                  {{ p.invoice?.number || '#' + p.invoice_id }}
                </router-link>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-4 py-3 text-sm">{{ p.project?.customer?.name || p.invoice?.client_name || '—' }}</td>
              <td class="px-4 py-3">
                <a v-if="p.receipt_path" :href="p.receipt_path" target="_blank" class="text-blue-600 text-xs font-medium hover:underline">عرض</a>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs max-w-[140px] truncate">{{ p.notes || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button type="button" class="text-blue-600 hover:text-blue-800" title="عرض في المتصفح" @click="viewPdf(p)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <button type="button" class="text-emerald-600 hover:text-emerald-800" title="تصدير PDF" @click="downloadPdf(p)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
                    </svg>
                  </button>
                  <button
                    v-if="p.project?.status !== 'completed'"
                    type="button"
                    class="text-slate-600 hover:text-slate-800"
                    title="تعديل"
                    @click="openEdit(p)"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button
                    v-if="p.project?.status !== 'completed'"
                    type="button"
                    class="text-red-500 hover:text-red-700"
                    title="حذف"
                    @click="confirmDelete(p)"
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

    <!-- Create payment modal -->
    <Teleport to="body">
      <div v-if="showCreate" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-6" dir="rtl" @click.self="closeCreate">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] overflow-hidden flex flex-col">
          <div class="px-5 sm:px-6 py-4 border-b bg-amber-50 flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-bold text-slate-900">{{ editingPayment ? 'تعديل الدفعة' : 'دفعة عميل جديدة' }}</h3>
              <p class="text-xs text-slate-500">1. اختر العميل · 2. مشروع أو فاتورة</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" @click="closeCreate">×</button>
          </div>

          <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <!-- Customer (required first) -->
            <div>
              <label class="block text-xs text-gray-500 mb-1">العميل *</label>
              <div v-if="selectedCustomer" class="mb-2 p-2 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between gap-2">
                <div>
                  <span class="text-sm font-medium">{{ selectedCustomer.name }}</span>
                  <span v-if="selectedCustomer.phone" class="text-xs text-gray-500 block">{{ selectedCustomer.phone }}</span>
                </div>
                <button v-if="!editingPayment" type="button" class="text-xs text-red-600 shrink-0" @click="clearCustomer">إزالة</button>
              </div>
              <div v-if="!editingPayment" class="relative" ref="customerWrap">
                <input
                  v-model="customerQuery"
                  type="text"
                  class="sf-field"
                  placeholder="ابحث عن عميل..."
                  autocomplete="off"
                  @focus="customerOpen = true"
                  @keydown.escape="customerOpen = false"
                />
                <div
                  v-if="customerOpen"
                  class="absolute z-50 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg"
                >
                  <button
                    v-for="c in filteredCustomers"
                    :key="c.id"
                    type="button"
                    class="w-full text-right px-3 py-2 hover:bg-slate-50 border-b text-sm"
                    @mousedown.prevent="selectCustomer(c)"
                  >
                    {{ c.name }}
                    <span v-if="c.phone" class="text-gray-400">· {{ c.phone }}</span>
                  </button>
                  <p v-if="!filteredCustomers.length" class="p-3 text-sm text-gray-400 text-center">لا نتائج</p>
                </div>
              </div>
            </div>

            <div :class="{ 'opacity-50 pointer-events-none': !form.customer_id }">
            <div>
              <label class="block text-xs text-gray-500 mb-1">المشروع</label>
              <div v-if="selectedProject" class="mb-2 p-2 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center justify-between gap-2">
                <div>
                  <span class="text-sm font-medium">{{ selectedProject.title_ar || selectedProject.title }}</span>
                </div>
                <button type="button" class="text-xs text-red-600 shrink-0" @click="clearProject">إزالة</button>
              </div>
              <div class="relative" ref="projectWrap">
                <input
                  v-model="projectQuery"
                  type="text"
                  class="sf-field"
                  :placeholder="form.customer_id ? 'ابحث عن مشروع...' : 'اختر العميل أولاً'"
                  autocomplete="off"
                  :disabled="!form.customer_id"
                  @focus="projectOpen = !!form.customer_id"
                  @keydown.escape="projectOpen = false"
                />
                <div
                  v-if="projectOpen && form.customer_id"
                  class="absolute z-50 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg"
                >
                  <button
                    v-for="p in filteredProjects"
                    :key="p.id"
                    type="button"
                    class="w-full text-right px-3 py-2 hover:bg-amber-50 border-b text-sm"
                    @mousedown.prevent="selectProject(p)"
                  >
                    {{ p.title_ar || p.title }}
                  </button>
                  <p v-if="!filteredProjects.length" class="p-3 text-sm text-gray-400 text-center">لا مشاريع لهذا العميل</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs text-gray-500 mb-1">الفاتورة</label>
              <div v-if="selectedInvoice" class="mb-2 p-2 bg-blue-50 rounded-lg border border-blue-100 flex items-center justify-between gap-2">
                <div>
                  <span class="text-sm font-medium font-mono">{{ selectedInvoice.number }}</span>
                  <span class="text-xs text-gray-500 block">{{ selectedInvoice.client_name }}</span>
                </div>
                <button type="button" class="text-xs text-red-600 shrink-0" @click="clearInvoice">إزالة</button>
              </div>
              <div class="relative" ref="invoiceWrap">
                <input
                  v-model="invoiceQuery"
                  type="text"
                  class="sf-field"
                  :placeholder="form.customer_id ? 'ابحث عن فاتورة...' : 'اختر العميل أولاً'"
                  autocomplete="off"
                  :disabled="!form.customer_id"
                  @focus="invoiceOpen = !!form.customer_id"
                  @keydown.escape="invoiceOpen = false"
                />
                <div
                  v-if="invoiceOpen && form.customer_id"
                  class="absolute z-50 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg"
                >
                  <button
                    v-for="inv in filteredInvoices"
                    :key="inv.id"
                    type="button"
                    class="w-full text-right px-3 py-2 hover:bg-blue-50 border-b text-sm"
                    @mousedown.prevent="selectInvoice(inv)"
                  >
                    <span class="font-mono">{{ inv.number }}</span>
                    <span class="text-gray-400">· {{ money(Number(inv.total || inv.amount || 0)) }}</span>
                  </button>
                  <p v-if="!filteredInvoices.length" class="p-3 text-sm text-gray-400 text-center">لا فواتير لهذا العميل</p>
                </div>
              </div>
            </div>
            </div>

            <p v-if="form.customer_id && !form.project_id && !form.invoice_id" class="text-xs text-rose-600">* اختر مشروعاً أو فاتورة على الأقل</p>
            <p v-if="!form.customer_id" class="text-xs text-rose-600">* اختر العميل أولاً</p>

            <div v-if="finance && form.project_id" class="grid grid-cols-3 gap-2 rounded-xl border border-amber-100 bg-amber-50/60 p-3 text-center">
              <div>
                <p class="text-[10px] text-amber-700/80">قيمة العقد</p>
                <p class="text-sm font-bold text-amber-950">{{ money(finance.contract_value) }}</p>
              </div>
              <div>
                <p class="text-[10px] text-amber-700/80">مدفوع</p>
                <p class="text-sm font-bold text-amber-950">{{ money(finance.payments_total) }}</p>
              </div>
              <div>
                <p class="text-[10px] text-amber-700/80">المتبقي</p>
                <p class="text-sm font-bold text-amber-900">{{ money(finance.balance_due) }}</p>
              </div>
            </div>
            <p v-else-if="financeLoading" class="text-xs text-gray-400 text-center">جاري تحميل ملخص المشروع...</p>

            <div>
              <label class="sf-label">المبلغ *</label>
              <input v-model.number="form.amount" type="number" min="0.01" step="0.01" class="sf-field" placeholder="0.00" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="sf-label">نوع الدفع</label>
                <select v-model="form.payment_type" class="sf-field">
                  <option value="cash">كاش</option>
                  <option value="bank">تحويل بنكي</option>
                  <option value="card">بطاقة</option>
                  <option value="transfer">حوالة</option>
                  <option value="cheque">شيك</option>
                  <option value="other">أخرى</option>
                </select>
              </div>
              <div>
                <label class="sf-label">التاريخ</label>
                <input v-model="form.paid_at" type="date" class="sf-field" />
              </div>
            </div>
            <div>
              <label class="sf-label">صورة الوصل</label>
              <input type="file" accept="image/*,application/pdf" class="sf-field" @change="onReceiptFile" />
            </div>
            <div>
              <label class="sf-label">ملاحظة</label>
              <input v-model="form.notes" type="text" class="sf-field" placeholder="اختياري" />
            </div>
          </div>

          <div class="px-5 sm:px-6 py-4 border-t bg-white flex gap-3">
            <button type="button" class="flex-1 border border-slate-300 py-2.5 rounded-lg text-sm" @click="closeCreate">إلغاء</button>
            <button
              type="button"
              class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60"
              :disabled="saving"
              @click="savePayment"
            >
              {{ saving ? 'جاري الحفظ...' : 'حفظ الدفعة' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <p class="text-lg font-bold mb-2">حذف الدفعة؟</p>
          <p class="text-sm text-gray-500 mb-6">{{ money(deleteTarget.amount) }} — {{ formatDate(deleteTarget.paid_at) }}</p>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/api'
import { downloadReceiptPdf } from '@/lib/receiptPdf'

const router = useRouter()

interface ProjectOption {
  id: number
  title: string
  title_ar?: string
  status?: string
  customer_id?: number | null
  customer?: { id: number; name: string } | null
}

interface PaymentRow {
  id: number
  project_id: number | null
  invoice_id?: number | null
  amount: number
  payment_type: string
  paid_at: string
  notes?: string | null
  receipt_path?: string | null
  project?: ProjectOption | null
  invoice?: InvoiceOption | null
}

interface InvoiceOption {
  id: number
  number: string
  client_name: string
  total?: number
  amount?: number
  project_id?: number | null
  quotation?: { customer_id?: number | null } | null
  project?: { customer_id?: number | null } | null
}

interface CustomerOption {
  id: number
  name: string
  phone?: string | null
}

interface FinanceSummary {
  contract_value: number
  payments_total: number
  balance_due: number
}

const list = ref<PaymentRow[]>([])
const projects = ref<ProjectOption[]>([])
const invoices = ref<InvoiceOption[]>([])
const customers = ref<CustomerOption[]>([])
const loading = ref(true)
const search = ref('')
const showCreate = ref(false)
const saving = ref(false)
const deleteTarget = ref<PaymentRow | null>(null)
const editingPayment = ref<PaymentRow | null>(null)

const customerQuery = ref('')
const customerOpen = ref(false)
const customerWrap = ref<HTMLElement | null>(null)
const projectQuery = ref('')
const projectOpen = ref(false)
const projectWrap = ref<HTMLElement | null>(null)
const invoiceQuery = ref('')
const invoiceOpen = ref(false)
const invoiceWrap = ref<HTMLElement | null>(null)
const finance = ref<FinanceSummary | null>(null)
const financeLoading = ref(false)

const form = ref({
  customer_id: null as number | null,
  project_id: null as number | null,
  invoice_id: null as number | null,
  amount: null as number | null,
  payment_type: 'cash',
  paid_at: todayStr(),
  notes: '',
  receipt: null as File | null,
})

const selectedCustomer = computed(() =>
  customers.value.find((c) => c.id === form.value.customer_id) || null
)

const selectedProject = computed(() =>
  projects.value.find((p) => p.id === form.value.project_id) || null
)

const selectedInvoice = computed(() =>
  invoices.value.find((i) => i.id === form.value.invoice_id) || null
)

const invoiceBelongsToCustomer = (inv: InvoiceOption, customerId: number, customerName: string) => {
  if (inv.quotation?.customer_id === customerId) return true
  if (inv.project?.customer_id === customerId) return true
  if (customerName && inv.client_name?.trim() === customerName.trim()) return true
  return false
}

const filteredCustomers = computed(() => {
  const q = customerQuery.value.trim().toLowerCase()
  if (!q) return customers.value.slice(0, 40)
  return customers.value.filter((c) =>
    c.name.toLowerCase().includes(q) ||
    (c.phone || '').includes(q)
  ).slice(0, 40)
})

const filteredInvoices = computed(() => {
  if (!form.value.customer_id) return []
  const customer = selectedCustomer.value
  const q = invoiceQuery.value.trim().toLowerCase()
  let base = invoices.value.filter((i) =>
    invoiceBelongsToCustomer(i, form.value.customer_id!, customer?.name || '')
  )
  if (form.value.project_id) {
    base = base.filter((i) => !i.project_id || i.project_id === form.value.project_id)
  }
  if (!q) return base.slice(0, 40)
  return base.filter((i) =>
    i.number.toLowerCase().includes(q) ||
    i.client_name.toLowerCase().includes(q)
  ).slice(0, 40)
})

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return list.value
  return list.value.filter((x) =>
    String(x.amount).includes(q) ||
    (x.notes || '').toLowerCase().includes(q) ||
    (x.project?.title_ar || '').toLowerCase().includes(q) ||
    (x.project?.title || '').toLowerCase().includes(q) ||
    (x.project?.customer?.name || '').toLowerCase().includes(q) ||
    paymentTypeLabel(x.payment_type).includes(q)
  )
})

const filteredProjects = computed(() => {
  const customerId = form.value.customer_id
  if (!customerId) return []
  const q = projectQuery.value.trim().toLowerCase()
  const listP = projects.value.filter((p) => {
    if (p.status === 'completed') return false
    const pid = p.customer_id ?? p.customer?.id
    return Number(pid) === Number(customerId)
  })
  if (!q) return listP.slice(0, 40)
  return listP.filter((p) =>
    (p.title_ar || '').toLowerCase().includes(q) ||
    p.title.toLowerCase().includes(q)
  ).slice(0, 40)
})

function todayStr() {
  return new Date().toISOString().slice(0, 10)
}

const money = (n: number) =>
  `AED ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const formatDate = (d: string) => {
  try { return new Date(d).toLocaleDateString('en-GB') } catch { return d }
}

const paymentTypeLabel = (t: string) =>
  ({ cash: 'كاش', bank: 'بنكي', card: 'بطاقة', transfer: 'حوالة', cheque: 'شيك', other: 'أخرى' } as Record<string, string>)[t] || t

const fetchList = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/payments')
    list.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const loadProjects = async () => {
  try {
    const [active, completed] = await Promise.all([
      api.get('/admin/projects', { params: { tab: 'active' } }),
      api.get('/admin/projects', { params: { tab: 'completed' } }),
    ])
    const map = new Map<number, ProjectOption>()
    ;[...(Array.isArray(active.data) ? active.data : []), ...(Array.isArray(completed.data) ? completed.data : [])]
      .forEach((p: ProjectOption) => map.set(p.id, p))
    projects.value = Array.from(map.values())
  } catch (e) {
    console.error(e)
  }
}

const loadFinance = async (projectId: number) => {
  financeLoading.value = true
  finance.value = null
  try {
    const res = await api.get(`/admin/projects/${projectId}/finance`)
    finance.value = {
      contract_value: Number(res.data.contract_value || 0),
      payments_total: Number(res.data.payments_total || 0),
      balance_due: Number(res.data.balance_due || 0),
    }
  } catch (e) {
    console.error(e)
  } finally {
    financeLoading.value = false
  }
}

const loadInvoices = async () => {
  try {
    const res = await api.get('/admin/invoices')
    invoices.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  }
}

const loadCustomers = async () => {
  try {
    const res = await api.get('/admin/customers')
    customers.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  }
}

const ensureCustomerInList = (c: CustomerOption | null | undefined) => {
  if (!c?.id) return
  if (!customers.value.some((x) => x.id === c.id)) {
    customers.value = [c, ...customers.value]
  }
}

const resolveCustomerIdFromPayment = (p: PaymentRow): number | null => {
  if (p.project?.customer?.id) return p.project.customer.id
  const inv = invoices.value.find((i) => i.id === p.invoice_id)
  if (inv?.quotation?.customer_id) return inv.quotation.customer_id
  if (inv?.project?.customer_id) return inv.project.customer_id
  const byName = customers.value.find((c) => c.name === p.invoice?.client_name || c.name === p.project?.customer?.name)
  return byName?.id || null
}

const openCreate = async () => {
  editingPayment.value = null
  form.value = {
    customer_id: null,
    project_id: null,
    invoice_id: null,
    amount: null,
    payment_type: 'cash',
    paid_at: todayStr(),
    notes: '',
    receipt: null,
  }
  customerQuery.value = ''
  projectQuery.value = ''
  invoiceQuery.value = ''
  finance.value = null
  showCreate.value = true
  await Promise.all([
    projects.value.length ? Promise.resolve() : loadProjects(),
    invoices.value.length ? Promise.resolve() : loadInvoices(),
    customers.value.length ? Promise.resolve() : loadCustomers(),
  ])
}

const openEdit = async (p: PaymentRow) => {
  editingPayment.value = p
  if (!invoices.value.length) await loadInvoices()
  if (!customers.value.length) await loadCustomers()
  const customerId = resolveCustomerIdFromPayment(p)
  if (p.project?.customer) ensureCustomerInList(p.project.customer as CustomerOption)
  form.value = {
    customer_id: customerId,
    project_id: p.project_id,
    invoice_id: p.invoice_id || null,
    amount: p.amount,
    payment_type: p.payment_type,
    paid_at: String(p.paid_at).slice(0, 10),
    notes: p.notes || '',
    receipt: null,
  }
  customerQuery.value = ''
  projectQuery.value = ''
  invoiceQuery.value = ''
  showCreate.value = true
  if (p.project_id) await loadFinance(p.project_id)
}

const closeCreate = () => {
  showCreate.value = false
  projectOpen.value = false
  invoiceOpen.value = false
  customerOpen.value = false
}

const selectCustomer = (c: CustomerOption) => {
  form.value.customer_id = c.id
  form.value.project_id = null
  form.value.invoice_id = null
  finance.value = null
  customerQuery.value = ''
  customerOpen.value = false
  projectQuery.value = ''
  invoiceQuery.value = ''
}

const clearCustomer = () => {
  form.value.customer_id = null
  form.value.project_id = null
  form.value.invoice_id = null
  finance.value = null
  customerQuery.value = ''
  projectQuery.value = ''
  invoiceQuery.value = ''
}

const selectProject = async (p: ProjectOption) => {
  form.value.project_id = p.id
  projectQuery.value = ''
  projectOpen.value = false
  await loadFinance(p.id)
}

const selectInvoice = async (inv: InvoiceOption) => {
  form.value.invoice_id = inv.id
  invoiceQuery.value = ''
  invoiceOpen.value = false
  if (inv.project_id && !form.value.project_id) {
    form.value.project_id = inv.project_id
    await loadFinance(inv.project_id)
  }
}

const clearInvoice = () => {
  form.value.invoice_id = null
  invoiceQuery.value = ''
}

const clearProject = () => {
  form.value.project_id = null
  finance.value = null
  projectQuery.value = ''
}

const onReceiptFile = (e: Event) => {
  form.value.receipt = (e.target as HTMLInputElement).files?.[0] || null
}

const savePayment = async () => {
  if (!form.value.customer_id) {
    alert('اختر العميل')
    return
  }
  if (!form.value.project_id && !form.value.invoice_id) {
    alert('اختر مشروعاً أو فاتورة')
    return
  }
  if (!form.value.amount) {
    alert('أدخل مبلغ الدفعة')
    return
  }
  saving.value = true
  try {
    if (editingPayment.value?.id && editingPayment.value.project_id) {
      await api.patch(`/admin/projects/${editingPayment.value.project_id}/payments/${editingPayment.value.id}`, {
        amount: form.value.amount,
        payment_type: form.value.payment_type,
        paid_at: form.value.paid_at || todayStr(),
        notes: form.value.notes || null,
        invoice_id: form.value.invoice_id,
      })
      await fetchList()
    } else {
      const fd = new FormData()
      if (form.value.project_id) fd.append('project_id', String(form.value.project_id))
      if (form.value.invoice_id) fd.append('invoice_id', String(form.value.invoice_id))
      fd.append('amount', String(form.value.amount))
      fd.append('payment_type', form.value.payment_type)
      fd.append('paid_at', form.value.paid_at || todayStr())
      if (form.value.notes) fd.append('notes', form.value.notes)
      if (form.value.receipt) fd.append('receipt', form.value.receipt)
      const res = await api.post('/admin/payments', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      const payment = res.data.payment as PaymentRow
      list.value = [payment, ...list.value.filter((x) => x.id !== payment.id)]
      if (res.data.finance) {
        finance.value = {
          contract_value: Number(res.data.finance.contract_value || 0),
          payments_total: Number(res.data.finance.payments_total || 0),
          balance_due: Number(res.data.finance.balance_due || 0),
        }
      }
    }
    showCreate.value = false
    editingPayment.value = null
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر حفظ الدفعة')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (p: PaymentRow) => { deleteTarget.value = p }

const downloadPdf = async (p: PaymentRow) => {
  if (!p.project_id) {
    alert('لا يوجد مشروع مرتبط بهذه الدفعة')
    return
  }
  try {
    await downloadReceiptPdf(p.project_id, p.id)
  } catch (e: any) {
    alert(e.message || e.response?.data?.message || 'تعذر تصدير الوصل')
  }
}

const viewPdf = (p: PaymentRow) => {
  if (!p.project_id) {
    alert('لا يوجد مشروع مرتبط بهذه الدفعة')
    return
  }
  router.push(`/admin/payments/receipt/${p.project_id}/${p.id}`)
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/admin/projects/${deleteTarget.value.project_id}/payments/${deleteTarget.value.id}`)
    list.value = list.value.filter((x) => x.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر الحذف')
  }
}

const onDocClick = (e: MouseEvent) => {
  if (!customerWrap.value?.contains(e.target as Node)) customerOpen.value = false
  if (!projectWrap.value?.contains(e.target as Node)) projectOpen.value = false
  if (!invoiceWrap.value?.contains(e.target as Node)) invoiceOpen.value = false
}

onMounted(async () => {
  document.addEventListener('click', onDocClick)
  await Promise.all([fetchList(), loadProjects(), loadInvoices(), loadCustomers()])
})
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>
