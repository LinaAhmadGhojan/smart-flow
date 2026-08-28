<template>
  <div dir="rtl" class="w-full max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <router-link to="/admin/invoices" class="text-gray-500 hover:text-gray-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </router-link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ invoice?.number || 'عرض الفاتورة' }}</h1>
          <p class="text-sm text-gray-500">صور المنتجات · الضريبة · رصيد العميل</p>
        </div>
      </div>
      <div v-if="invoice" class="flex flex-wrap gap-2">
        <button
          v-if="!invoice.project_id"
          type="button"
          class="border border-blue-600 text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium"
          @click="openLinkProject"
        >
          ربط بمشروع
        </button>
        <button
          type="button"
          class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60"
          :disabled="!canRecordPayment"
          @click="openPaymentForm()"
        >
          تسجيل دفعة
        </button>
        <button
          type="button"
          class="border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60"
          :disabled="pdfLoading"
          @click="downloadPdf"
        >
          {{ pdfLoading ? 'جاري التصدير...' : 'تصدير PDF' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="bg-white rounded-xl p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <template v-else-if="invoice">
      <div class="bg-white rounded-xl shadow-sm p-6 mb-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
        <div>
          <p class="text-xs text-gray-500">العميل</p>
          <p class="font-medium">{{ invoice.client_name }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-500">المشروع</p>
          <p class="font-medium">{{ invoice.project?.title_ar || invoice.project?.title || '—' }}</p>
          <router-link
            v-if="invoice.project_id"
            :to="`/admin/projects/${invoice.project_id}`"
            class="text-xs text-blue-600"
          >فتح المشروع</router-link>
          <button
            v-else
            type="button"
            class="text-xs text-blue-600 hover:underline"
            @click="openLinkProject"
          >+ ربط مشروع</button>
        </div>
        <div>
          <p class="text-xs text-gray-500">عرض السعر</p>
          <router-link
            v-if="invoice.quotation_id"
            :to="`/admin/quotations/${invoice.quotation_id}`"
            class="font-mono text-blue-600"
          >{{ invoice.quotation?.number || '#' + invoice.quotation_id }}</router-link>
          <p v-else class="text-gray-400">—</p>
          <router-link
            v-if="invoice.quotation?.project_id"
            :to="`/admin/projects/${invoice.quotation.project_id}`"
            class="text-xs text-emerald-600 block mt-1"
          >مشروع العرض: {{ invoice.quotation?.project?.title_ar || invoice.quotation?.project?.title || '—' }}</router-link>
        </div>
        <div>
          <p class="text-xs text-gray-500">التاريخ / الحالة</p>
          <p class="font-medium">{{ invoice.date }} · {{ statusLabel(invoice.status) }}</p>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
          <p class="text-xs text-blue-700 mb-1">إجمالي الفاتورة</p>
          <p class="text-xl font-bold text-blue-900">{{ money(totals.grand) }}</p>
        </div>
        <div class="rounded-xl bg-amber-50 border border-amber-100 p-4">
          <p class="text-xs text-amber-700 mb-1">مدفوع من العميل</p>
          <p class="text-xl font-bold text-amber-900">{{ money(totals.paid) }}</p>
        </div>
        <div class="rounded-xl border p-4" :class="totals.balance > 0 ? 'bg-rose-50 border-rose-100' : 'bg-emerald-50 border-emerald-100'">
          <p class="text-xs mb-1" :class="totals.balance > 0 ? 'text-rose-700' : 'text-emerald-700'">المتبقي (Balance Due)</p>
          <p class="text-xl font-bold" :class="totals.balance > 0 ? 'text-rose-900' : 'text-emerald-900'">{{ money(totals.balance) }}</p>
        </div>
        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
          <p class="text-xs text-slate-500 mb-1">الضريبة TAX {{ totals.taxPercent }}%</p>
          <p class="text-lg font-bold text-slate-800">{{ money(totals.tax) }}</p>
          <p v-if="totals.withholding > 0" class="text-[11px] text-slate-500 mt-1">خصم ضريبي: − {{ money(totals.withholding) }}</p>
        </div>
      </div>

      <div v-if="payments.length" class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b font-bold text-gray-900 flex items-center justify-between">
          <span>دفعات هذه الفاتورة</span>
          <button type="button" class="text-xs text-amber-700 hover:underline" @click="openPaymentForm()">+ دفعة</button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-gray-500">
              <tr>
                <th class="px-4 py-2 text-right">التاريخ</th>
                <th class="px-4 py-2 text-right">المبلغ</th>
                <th class="px-4 py-2 text-right">النوع</th>
                <th class="px-4 py-2 text-right">ملاحظة</th>
                <th class="px-4 py-2 text-center">إجراءات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in payments" :key="p.id" class="border-b border-gray-50">
                <td class="px-4 py-2">{{ p.paid_at }}</td>
                <td class="px-4 py-2 font-semibold text-amber-800">{{ money(p.amount) }}</td>
                <td class="px-4 py-2">{{ paymentTypeLabel(p.payment_type) }}</td>
                <td class="px-4 py-2 text-gray-500">{{ p.notes || '—' }}</td>
                <td class="px-4 py-2 text-center">
                  <button type="button" class="text-blue-600 text-xs hover:underline" @click="openPaymentForm(p)">تعديل</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b font-bold text-gray-900">بنود الفاتورة (مع صور المنتجات)</div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[820px]">
            <thead class="bg-gray-50 text-gray-500">
              <tr>
                <th class="px-3 py-2 text-right w-16">صورة</th>
                <th class="px-3 py-2 text-right">الكود</th>
                <th class="px-3 py-2 text-right">الوصف</th>
                <th class="px-3 py-2 text-right">Qty</th>
                <th class="px-3 py-2 text-right">Rate</th>
                <th class="px-3 py-2 text-right">خصم خاص</th>
                <th class="px-3 py-2 text-right">من الخصم الكلي</th>
                <th class="px-3 py-2 text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(item, idx) in items" :key="idx">
                <tr v-if="item.is_section" class="bg-slate-100">
                  <td colspan="8" class="px-3 py-2.5 font-bold text-slate-800 text-center">{{ item.description }}</td>
                </tr>
                <tr v-else class="border-b border-gray-50 hover:bg-slate-50/50">
                  <td class="px-3 py-2">
                    <div class="w-12 h-12 rounded-lg border border-gray-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                      <img v-if="itemImage(item)" :src="itemImage(item)!" alt="" class="w-full h-full object-cover" />
                      <span v-else class="text-[10px] text-gray-300">—</span>
                    </div>
                  </td>
                  <td class="px-3 py-2 font-mono text-xs">{{ item.code || '—' }}</td>
                  <td class="px-3 py-2 whitespace-pre-line">{{ item.description }}</td>
                  <td class="px-3 py-2">{{ item.quantity }}</td>
                  <td class="px-3 py-2">{{ money(item.rate) }}</td>
                  <td class="px-3 py-2 text-red-600 font-medium">{{ itemDiscountLabel(item) }}</td>
                  <td class="px-3 py-2 text-purple-700 font-medium text-xs">
                    <template v-if="itemGlobalShare(item) > 0">
                      <div>{{ globalDiscountRateLabel }}</div>
                      <div class="text-red-600">− {{ money(itemGlobalShare(item)) }}</div>
                    </template>
                    <span v-else>—</span>
                  </td>
                  <td class="px-3 py-2 font-medium">
                    <div v-if="Number(item.discount_amount || 0) > 0 || itemGlobalShare(item) > 0">
                      <div class="text-gray-400 line-through text-xs">{{ money(lineSubtotal(item)) }}</div>
                      <div v-if="itemGlobalShare(item) > 0" class="text-gray-500 text-xs">{{ money(item.amount) }}</div>
                      <div>{{ money(itemFinalAmount(item)) }}</div>
                    </div>
                    <span v-else>{{ money(item.amount) }}</span>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="px-5 py-4 border-t grid sm:grid-cols-2 gap-6">
          <div class="text-sm text-gray-500">
            <p v-if="invoice.notes" class="whitespace-pre-wrap"><span class="font-medium text-gray-700">ملاحظات:</span> {{ invoice.notes }}</p>
          </div>
          <div class="max-w-sm ms-auto text-sm space-y-1.5 w-full">
            <div v-if="totals.lineDiscountTotal > 0" class="flex justify-between text-red-600">
              <span>خصم البنود</span><span>− {{ money(totals.lineDiscountTotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>{{ money(totals.subtotal) }}</span></div>
            <div v-if="totals.globalDiscount > 0" class="flex justify-between text-red-600">
              <span>خصم كلي</span><span>− {{ money(totals.globalDiscount) }}</span>
            </div>
            <div class="flex justify-between text-gray-600"><span>TAX {{ totals.taxPercent }}%</span><span>{{ money(totals.tax) }}</span></div>
            <div v-if="totals.withholding > 0" class="flex justify-between text-rose-600">
              <span>Withholding {{ totals.withholdingPercent }}%</span>
              <span>− {{ money(totals.withholding) }}</span>
            </div>
            <div class="flex justify-between font-bold text-blue-700 text-base border-t pt-2">
              <span>الإجمالي</span>
              <span>{{ money(totals.grand) }}</span>
            </div>
            <div class="flex justify-between text-amber-800 border-t pt-2"><span>مدفوع</span><span>{{ money(totals.paid) }}</span></div>
            <div class="flex justify-between font-bold" :class="totals.balance > 0 ? 'text-rose-700' : 'text-emerald-700'">
              <span>المتبقي</span>
              <span>{{ money(totals.balance) }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Link project modal -->
    <Teleport to="body">
      <div v-if="showLinkProject" class="sf-modal-backdrop" dir="rtl" @click.self="showLinkProject = false">
        <div class="sf-modal-panel max-w-md">
          <h3 class="text-lg font-bold mb-4">ربط الفاتورة بمشروع</h3>
          <div v-if="selectedProject" class="mb-3 p-2 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center justify-between">
            <span class="text-sm font-medium">{{ selectedProject.title_ar || selectedProject.title }}</span>
            <button type="button" class="text-xs text-red-600" @click="clearSelectedProject">إزالة</button>
          </div>
          <div class="relative" ref="projectWrap">
            <input
              v-model="projectQuery"
              type="text"
              class="sf-field"
              placeholder="ابحث عن مشروع..."
              @focus="projectOpen = true"
            />
            <div v-if="projectOpen" class="absolute z-50 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg">
              <button
                v-for="p in filteredProjects"
                :key="p.id"
                type="button"
                class="w-full text-right px-3 py-2 hover:bg-blue-50 border-b text-sm"
                @mousedown.prevent="selectProject(p)"
              >
                {{ p.title_ar || p.title }}
              </button>
            </div>
          </div>
          <div class="flex gap-3 mt-6">
            <button type="button" class="flex-1 border py-2.5 rounded-lg text-sm" @click="showLinkProject = false">إلغاء</button>
            <button type="button" class="flex-1 bg-blue-600 text-white py-2.5 rounded-lg text-sm disabled:opacity-60" :disabled="linkSaving || !linkProjectId" @click="saveProjectLink">
              {{ linkSaving ? 'جاري الحفظ...' : 'ربط' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Payment modal -->
    <Teleport to="body">
      <div v-if="showPaymentForm" class="sf-modal-backdrop" dir="rtl" @click.self="showPaymentForm = false">
        <div class="sf-modal-panel max-w-lg">
          <h3 class="text-lg font-bold mb-1">{{ editingPayment ? 'تعديل الدفعة' : 'تسجيل دفعة للفاتورة' }}</h3>
          <p class="text-xs text-gray-500 mb-4">مرتبطة بالفاتورة {{ invoice?.number }} والمشروع</p>
          <div class="space-y-4">
            <div>
              <label class="sf-label">المبلغ *</label>
              <input v-model.number="paymentForm.amount" type="number" min="0.01" step="0.01" class="sf-field" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="sf-label">نوع الدفع</label>
                <select v-model="paymentForm.payment_type" class="sf-field">
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
                <input v-model="paymentForm.paid_at" type="date" class="sf-field" />
              </div>
            </div>
            <div>
              <label class="sf-label">ملاحظة</label>
              <input v-model="paymentForm.notes" type="text" class="sf-field" />
            </div>
          </div>
          <div class="flex gap-3 mt-6">
            <button type="button" class="flex-1 border py-2.5 rounded-lg text-sm" @click="showPaymentForm = false">إلغاء</button>
            <button type="button" class="flex-1 bg-amber-500 text-white py-2.5 rounded-lg text-sm disabled:opacity-60" :disabled="paymentSaving" @click="savePayment">
              {{ paymentSaving ? 'جاري الحفظ...' : 'حفظ' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api'
import { allocateGlobalDiscount, computeGlobalDiscount } from '@/lib/quotationDiscount'

interface ProjectOption {
  id: number
  title: string
  title_ar?: string
}

interface PaymentRow {
  id: number
  project_id?: number | null
  invoice_id?: number | null
  amount: number
  payment_type: string
  paid_at: string
  notes?: string | null
}

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const pdfLoading = ref(false)
const invoice = ref<any>(null)

const showLinkProject = ref(false)
const linkProjectId = ref<number | null>(null)
const linkSaving = ref(false)
const projects = ref<ProjectOption[]>([])
const projectQuery = ref('')
const projectOpen = ref(false)
const projectWrap = ref<HTMLElement | null>(null)

const showPaymentForm = ref(false)
const paymentSaving = ref(false)
const editingPayment = ref<PaymentRow | null>(null)
const paymentForm = ref({
  amount: null as number | null,
  payment_type: 'cash',
  paid_at: new Date().toISOString().slice(0, 10),
  notes: '',
})

const items = computed(() => invoice.value?.quotation?.items || [])
const productItems = computed(() => items.value.filter((i: any) => !i.is_section))

const globalShareByItemId = computed(() => {
  const q = invoice.value?.quotation
  const map = new Map<number, number>()
  if (!q) return map

  const amounts = productItems.value.map((i: any) => Number(i.amount || 0))
  const subtotal = amounts.reduce((s, n) => s + n, 0)
  const global = computeGlobalDiscount(
    subtotal,
    q.discount_type === 'percent' || q.discount_type === 'fixed' ? q.discount_type : '',
    q.discount_value
  )
  const shares = allocateGlobalDiscount(global, amounts)
  productItems.value.forEach((item: any, i: number) => {
    if (item.id) map.set(item.id, shares[i] ?? 0)
  })
  return map
})

const globalDiscountRateLabel = computed(() => {
  const q = invoice.value?.quotation
  if (q?.discount_type === 'percent' && q.discount_value) return `${Number(q.discount_value)}%`
  if (q?.discount_type === 'fixed' && Number(q.discount_amount || 0) > 0) return 'حصة من الكلي'
  return ''
})

const itemGlobalShare = (item: any) => globalShareByItemId.value.get(item.id) ?? 0
const itemFinalAmount = (item: any) => Math.max(0, Number(item.amount || 0) - itemGlobalShare(item))

const payments = computed(() => invoice.value?.payments || [])

const totals = computed(() => {
  const q = invoice.value?.quotation
  const fin = invoice.value?.payment_summary || {}
  const lineDiscountTotal = items.value
    .filter((i: any) => !i.is_section)
    .reduce((s: number, i: any) => s + Number(i.discount_amount || 0), 0)
  const globalDiscount = Number(q?.discount_amount ?? 0)
  const discountTotal = lineDiscountTotal + globalDiscount
  const subtotal = Number(q?.subtotal ?? invoice.value?.amount ?? 0)
  const taxPercent = Number(q?.tax_percent ?? invoice.value?.tax_percent ?? 0)
  const tax = Number(q?.tax_amount ?? invoice.value?.tax_amount ?? 0)
  const withholdingPercent = Number(q?.withholding_tax_percent ?? 0)
  const withholding = Number(q?.withholding_tax_amount ?? 0)
  const grand = Number(q?.total ?? invoice.value?.total ?? invoice.value?.amount ?? 0)
  const paid = Number(fin.paid ?? 0)
  const balance = Number(fin.balance_due ?? Math.max(0, grand - paid))
  return { subtotal, lineDiscountTotal, globalDiscount, discountTotal, taxPercent, tax, withholdingPercent, withholding, grand, paid, balance }
})

const canRecordPayment = computed(() => !!invoice.value?.project_id || !!invoice.value?.quotation?.project_id)

const selectedProject = computed(() =>
  projects.value.find((p) => p.id === linkProjectId.value) || null
)

const filteredProjects = computed(() => {
  const q = projectQuery.value.trim().toLowerCase()
  if (!q) return projects.value.slice(0, 30)
  return projects.value.filter((p) =>
    (p.title_ar || '').toLowerCase().includes(q) || p.title.toLowerCase().includes(q)
  ).slice(0, 30)
})

const money = (n: number) =>
  `${invoice.value?.currency || 'AED'} ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const statusLabel = (s?: string) =>
  ({ draft: 'مسودة', sent: 'مرسلة', paid: 'مدفوعة', cancelled: 'ملغاة' } as Record<string, string>)[s || ''] || (s || '—')

const paymentTypeLabel = (t: string) =>
  ({ cash: 'كاش', bank: 'بنكي', card: 'بطاقة', transfer: 'حوالة', cheque: 'شيك', other: 'أخرى' } as Record<string, string>)[t] || t

const itemImage = (item: any): string | null => {
  const raw = item?.product?.image || item?.image || null
  if (!raw) return null
  if (String(raw).startsWith('http') || String(raw).startsWith('/')) return String(raw)
  return `/storage/${String(raw).replace(/^\/?storage\//, '')}`
}

const lineSubtotal = (item: any) => Number(item.quantity || 0) * Number(item.rate || 0)

const itemDiscountLabel = (item: any) => {
  const amt = Number(item.discount_amount || 0)
  if (!amt) return '—'
  if (item.discount_type === 'percent') return `-${Number(item.discount_value || 0)}%`
  return `-${money(amt)}`
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

const load = async () => {
  loading.value = true
  try {
    const res = await api.get(`/admin/invoices/${route.params.id}`)
    invoice.value = res.data
  } catch {
    alert('تعذر تحميل الفاتورة')
    router.push('/admin/invoices')
  } finally {
    loading.value = false
  }
}

const openLinkProject = async () => {
  linkProjectId.value = invoice.value?.project_id || invoice.value?.quotation?.project_id || null
  projectQuery.value = ''
  showLinkProject.value = true
  if (!projects.value.length) await loadProjects()
}

const selectProject = (p: ProjectOption) => {
  linkProjectId.value = p.id
  projectQuery.value = ''
  projectOpen.value = false
}

const clearSelectedProject = () => {
  linkProjectId.value = null
  projectQuery.value = ''
}

const saveProjectLink = async () => {
  if (!linkProjectId.value) return
  linkSaving.value = true
  try {
    const res = await api.put(`/admin/invoices/${route.params.id}`, { project_id: linkProjectId.value })
    invoice.value = { ...invoice.value, ...res.data }
    showLinkProject.value = false
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر ربط المشروع')
  } finally {
    linkSaving.value = false
  }
}

const resolveProjectId = () =>
  invoice.value?.project_id || invoice.value?.quotation?.project_id || null

const openPaymentForm = (p?: PaymentRow) => {
  const projectId = resolveProjectId()
  if (!projectId) {
    alert('اربط الفاتورة بمشروع أولاً')
    openLinkProject()
    return
  }
  editingPayment.value = p || null
  paymentForm.value = p
    ? { amount: p.amount, payment_type: p.payment_type, paid_at: String(p.paid_at).slice(0, 10), notes: p.notes || '' }
    : { amount: totals.value.balance > 0 ? totals.value.balance : null, payment_type: 'cash', paid_at: new Date().toISOString().slice(0, 10), notes: '' }
  showPaymentForm.value = true
}

const savePayment = async () => {
  const projectId = resolveProjectId()
  if (!projectId || !paymentForm.value.amount) {
    alert('أدخل المبلغ')
    return
  }
  paymentSaving.value = true
  try {
    if (editingPayment.value?.id) {
      await api.patch(`/admin/projects/${projectId}/payments/${editingPayment.value.id}`, {
        amount: paymentForm.value.amount,
        payment_type: paymentForm.value.payment_type,
        paid_at: paymentForm.value.paid_at,
        notes: paymentForm.value.notes || null,
        invoice_id: invoice.value.id,
      })
    } else {
      await api.post(`/admin/projects/${projectId}/payments`, {
        amount: paymentForm.value.amount,
        payment_type: paymentForm.value.payment_type,
        paid_at: paymentForm.value.paid_at,
        notes: paymentForm.value.notes || null,
        invoice_id: invoice.value.id,
      })
    }
    showPaymentForm.value = false
    await load()
  } catch (e: any) {
    alert(e.response?.data?.message || 'تعذر حفظ الدفعة')
  } finally {
    paymentSaving.value = false
  }
}

const downloadPdf = async () => {
  pdfLoading.value = true
  try {
    const res = await api.get(`/admin/invoices/${route.params.id}/pdf`, {
      responseType: 'blob',
      headers: { Accept: 'application/pdf' },
    })
    const type = res.headers['content-type'] || ''
    if (type.includes('application/json')) {
      const text = await (res.data as Blob).text()
      throw new Error(JSON.parse(text).message || 'PDF error')
    }
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${invoice.value?.number || 'invoice'}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e: any) {
    alert(e?.message || 'تعذر تصدير PDF')
  } finally {
    pdfLoading.value = false
  }
}

const onDocClick = (e: MouseEvent) => {
  if (!projectWrap.value?.contains(e.target as Node)) projectOpen.value = false
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  load()
})
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>
