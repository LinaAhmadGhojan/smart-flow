<template>
  <div dir="rtl" class="w-full min-w-0 max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <router-link to="/admin/quotations" class="text-gray-500 hover:text-gray-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </router-link>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ isNew ? 'عرض سعر جديد' : (form.number || 'تعديل عرض السعر') }}
        </h1>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          v-if="!isNew && invoices.length === 0"
          type="button"
          @click="showInvoiceModal = true"
          class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium"
        >
          تحويل لفاتورة
        </button>
        <router-link
          v-if="!isNew && invoices.length > 0"
          :to="`/admin/invoices/${invoices[0].id}`"
          class="border border-amber-500 text-amber-700 hover:bg-amber-50 px-4 py-2 rounded-lg text-sm font-medium"
        >
          عرض الفاتورة
        </router-link>
        <button
          v-if="!isNew"
          type="button"
          @click="downloadPdf"
          class="border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-4 py-2 rounded-lg text-sm font-medium"
        >
          تصدير PDF
        </button>
        <button
          type="button"
          @click="save"
          :disabled="saving"
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-4 py-2 rounded-lg text-sm font-medium"
        >
          {{ saving ? 'جاري الحفظ...' : 'حفظ' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="bg-white rounded-xl p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <template v-else>
      <div class="bg-white rounded-xl shadow-sm p-6 space-y-5 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">رقم العرض</label>
            <input v-model="form.number" type="text" placeholder="تلقائي إن تُرك فارغاً" class="sf-field" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">التاريخ</label>
            <input v-model="form.date" type="date" class="sf-field" required />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">الحالة</label>
            <select v-model="form.status" class="sf-field">
              <option value="draft">مسودة</option>
              <option value="sent">مرسل</option>
              <option value="accepted">مقبول</option>
              <option value="cancelled">ملغى</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">العملة</label>
            <input v-model="form.currency" type="text" class="sf-field" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">العميل</label>
            <div v-if="selectedCustomer" class="mb-2 p-2 bg-blue-50 rounded-lg border border-blue-100 flex items-center justify-between gap-2">
              <span class="text-sm font-medium">{{ selectedCustomer.name }}</span>
              <button type="button" class="text-xs text-red-600" @click="clearCustomer">إزالة</button>
            </div>
            <div class="relative" ref="customerWrap">
              <input
                v-model="customerQuery"
                type="text"
                class="sf-field"
                placeholder="ابحث عن عميل..."
                @focus="customerOpen = true"
                @keydown.escape="customerOpen = false"
              />
              <div v-if="customerOpen" class="absolute z-30 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg">
                <button
                  v-for="c in filteredCustomers"
                  :key="c.id"
                  type="button"
                  class="w-full text-right px-3 py-2 hover:bg-blue-50 border-b text-sm"
                  @click="selectCustomer(c)"
                >
                  {{ c.name }} <span v-if="c.phone" class="text-gray-400">· {{ c.phone }}</span>
                </button>
                <p v-if="!filteredCustomers.length" class="p-3 text-sm text-gray-400 text-center">لا نتائج</p>
              </div>
            </div>
            <input v-model="form.client_name" type="text" class="sf-field mt-2" required placeholder="اسم العميل في PDF" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">المشروع</label>
            <div v-if="selectedProject" class="mb-2 p-2 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center justify-between gap-2">
              <span class="text-sm font-medium">{{ selectedProject.title_ar || selectedProject.title }}</span>
              <button type="button" class="text-xs text-red-600" @click="clearProject">إزالة</button>
            </div>
            <div class="relative" ref="projectWrap">
              <input
                v-model="projectQuery"
                type="text"
                class="sf-field"
                placeholder="ابحث عن مشروع..."
                @focus="projectOpen = true"
                @keydown.escape="projectOpen = false"
              />
              <div v-if="projectOpen" class="absolute z-30 mt-1 w-full max-h-48 overflow-auto bg-white border rounded-xl shadow-lg">
                <button
                  v-for="p in filteredProjects"
                  :key="p.id"
                  type="button"
                  class="w-full text-right px-3 py-2 hover:bg-emerald-50 border-b text-sm"
                  @click="selectProject(p)"
                >
                  {{ p.title_ar || p.title }}
                  <span v-if="p.customer?.name" class="text-gray-400">· {{ p.customer.name }}</span>
                </button>
                <p v-if="!filteredProjects.length" class="p-3 text-sm text-gray-400 text-center">لا نتائج</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">TAX %</label>
            <input v-model.number="form.tax_percent" type="number" min="0" step="0.01" class="sf-field" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Withholding Tax %</label>
            <input v-model.number="form.withholding_tax_percent" type="number" min="0" step="0.01" class="sf-field" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">خصم كلي</label>
            <div class="flex gap-2">
              <select v-model="form.discount_type" class="sf-field w-24 shrink-0" @change="onGlobalDiscountTypeChange">
                <option value="">بدون</option>
                <option value="percent">%</option>
                <option value="fixed">AED</option>
              </select>
              <input
                v-if="form.discount_type"
                v-model.number="form.discount_value"
                type="number"
                min="0"
                :max="form.discount_type === 'percent' ? 100 : undefined"
                step="0.01"
                class="sf-field flex-1"
                :placeholder="form.discount_type === 'percent' ? '10' : '500'"
              />
              <input v-else type="text" class="sf-field flex-1 opacity-50" disabled placeholder="—" />
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs text-gray-500 mb-1">Comments</label>
          <textarea v-model="form.comments" rows="3" class="sf-field" placeholder="ملاحظات تظهر في الـ PDF"></textarea>
        </div>
      </div>

      <!-- Line items with section titles (plancher style) -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
          <h2 class="font-bold text-gray-900">البنود حسب المجموعات</h2>
          <button type="button" class="text-sm bg-slate-800 text-white px-3 py-1.5 rounded-lg" @click="addSection">
            + عنوان مجموعة
          </button>
        </div>

        <div v-if="!sections.length" class="text-center py-8 text-gray-400 text-sm border border-dashed rounded-xl mb-4">
          اضغط «+ عنوان مجموعة» ثم أضف المنتجات تحت كل عنوان
        </div>

        <div v-for="(sec, sIdx) in sections" :key="'sec-' + sIdx" class="mb-5 border border-gray-200 rounded-xl overflow-hidden">
          <div class="bg-slate-100 px-4 py-3 flex items-center gap-3 border-b border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide shrink-0">عنوان</span>
            <input
              v-model="sec.title"
              type="text"
              class="flex-1 bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm font-bold text-slate-900"
              placeholder="مثال: الكاميرات / الأسلاك / التركيب..."
            />
            <button
              type="button"
              class="text-xs px-3 py-1.5 rounded-lg font-medium shrink-0 transition-colors"
              :class="sec.pickerOpen ? 'bg-blue-700 text-white ring-2 ring-blue-300' : 'bg-blue-600 text-white hover:bg-blue-700'"
              @click="openSectionPicker(sIdx)"
            >
              إضافة هنا
            </button>
            <button type="button" class="text-red-500 text-lg leading-none px-1" title="حذف المجموعة" @click="removeSection(sIdx)">×</button>
          </div>

          <div class="section-product-picker px-4 py-3 bg-blue-50/60 border-b border-blue-100 relative">
            <label class="block text-xs text-gray-600 mb-1.5">
              إضافة منتج
              <span class="text-blue-700 font-bold">→ {{ sectionTitleLabel(sec, sIdx) }}</span>
            </label>
            <input
              :id="'section-search-' + sIdx"
              v-model="sec.productQuery"
              type="text"
              class="sf-field !bg-white"
              placeholder="ابحث بالاسم (عربي / English) أو الكود أو الوصف..."
              autocomplete="off"
              @focus="openSectionPicker(sIdx)"
              @input="openSectionPicker(sIdx)"
              @keydown.escape="closeSectionPicker(sec)"
              @keydown.enter.prevent="pickFirstFiltered(sec, sIdx)"
            />
            <div
              v-if="sec.pickerOpen"
              class="absolute z-40 mt-1 left-4 right-4 max-h-72 overflow-auto bg-white border border-blue-200 rounded-xl shadow-xl"
            >
              <button
                v-for="p in filteredProductsForSection(sec)"
                :key="'pick-' + sIdx + '-' + p.id"
                type="button"
                class="w-full text-right px-3 py-2.5 hover:bg-blue-50 border-b flex items-center gap-3"
                @click="addProduct(p, sIdx)"
              >
                <img :src="mediaUrl(p.image, '/logo.jpeg')" class="w-10 h-10 rounded-lg object-cover border" @error="handleMediaError" />
                <div class="min-w-0 flex-1">
                  <div class="flex items-baseline gap-2 flex-wrap">
                    <span class="text-sm font-medium">{{ productDisplayName(p) }}</span>
                    <span class="font-mono text-[11px] text-gray-400">{{ productCode(p) }}</span>
                  </div>
                  <p v-if="productEnglishSubtitle(p)" class="text-xs text-gray-500 truncate">{{ productEnglishSubtitle(p) }}</p>
                </div>
                <span class="text-sm font-semibold text-blue-700">{{ money(productPrice(p)) }}</span>
              </button>
              <p v-if="!filteredProductsForSection(sec).length" class="p-3 text-sm text-gray-400 text-center">لا نتائج</p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[980px]">
              <thead>
                <tr class="text-gray-500 border-b bg-gray-50">
                  <th class="py-2 px-3 text-right font-medium w-24">الكود</th>
                  <th class="py-2 px-3 text-right font-medium">الاسم / الوصف</th>
                  <th class="py-2 px-3 text-right font-medium w-28">صورة</th>
                  <th class="py-2 px-3 text-right font-medium w-24">Qty</th>
                  <th class="py-2 px-3 text-right font-medium w-28">Rate</th>
                  <th class="py-2 px-3 text-right font-medium w-32">خصم خاص</th>
                  <th class="py-2 px-3 text-right font-medium w-36">من الخصم الكلي</th>
                  <th class="py-2 px-3 text-right font-medium w-32">Amount</th>
                  <th class="py-2 px-3 w-10"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!sec.items.length">
                  <td colspan="9" class="py-6 text-center text-gray-400 text-xs">لا منتجات في هذه المجموعة بعد</td>
                </tr>
                <tr v-for="(item, idx) in sec.items" :key="'s' + sIdx + '-i' + idx" class="border-b border-gray-50 align-top">
                  <td class="py-2 px-3 font-mono text-xs">{{ item.code }}</td>
                  <td class="py-2 px-3"><div class="font-medium whitespace-pre-line leading-relaxed">{{ item.description }}</div></td>
                  <td class="py-2 px-3">
                    <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 border">
                      <img v-if="itemImage(item)" :src="mediaUrl(itemImage(item), '/logo.jpeg')" alt="" class="w-full h-full object-cover" @error="handleMediaError" />
                    </div>
                  </td>
                  <td class="py-2 px-3"><input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="sf-field !py-1.5" /></td>
                  <td class="py-2 px-3"><input v-model.number="item.rate" type="number" min="0" step="0.01" class="sf-field !py-1.5" /></td>
                  <td class="py-2 px-3">
                    <select v-model="item.discount_type" class="sf-field !py-1.5 text-xs mb-1" @change="onDiscountTypeChange(item)">
                      <option value="">بدون</option>
                      <option value="percent">%</option>
                      <option value="fixed">AED</option>
                    </select>
                    <input
                      v-if="item.discount_type"
                      v-model.number="item.discount_value"
                      type="number"
                      min="0"
                      :max="item.discount_type === 'percent' ? 100 : undefined"
                      step="0.01"
                      class="sf-field !py-1.5 text-xs"
                      :placeholder="item.discount_type === 'percent' ? '3' : '50'"
                    />
                    <p v-if="lineDiscountAmount(item) > 0" class="text-red-600 text-[11px] mt-1">−{{ discountLabel(item) }}</p>
                  </td>
                  <td class="py-2 px-3 pt-3">
                    <div v-if="lineGlobalShare(sIdx, idx) > 0" class="text-purple-700 text-xs font-semibold leading-relaxed">
                      <div>{{ globalDiscountRateLabel }}</div>
                      <div class="text-red-600 mt-0.5">− {{ money(lineGlobalShare(sIdx, idx)) }}</div>
                    </div>
                    <span v-else class="text-gray-400 text-xs">—</span>
                  </td>
                  <td class="py-2 px-3 whitespace-nowrap pt-3">
                    <div v-if="lineDiscountAmount(item) > 0 || lineGlobalShare(sIdx, idx) > 0" class="space-y-0.5">
                      <div class="text-gray-400 line-through text-xs">{{ money(lineSubtotal(item)) }}</div>
                      <div v-if="lineDiscountAmount(item) > 0 && !lineGlobalShare(sIdx, idx)" class="font-semibold text-emerald-700">{{ money(lineAmount(item)) }}</div>
                      <div v-else-if="lineGlobalShare(sIdx, idx) > 0" class="text-gray-500 text-xs">{{ money(lineAmount(item)) }}</div>
                      <div class="font-semibold text-emerald-700">{{ money(lineFinalAmount(item, sIdx, idx)) }}</div>
                    </div>
                    <span v-else>{{ money(lineAmount(item)) }}</span>
                  </td>
                  <td class="py-2 px-3">
                    <button type="button" class="text-red-400 hover:text-red-600" @click="sec.items.splice(idx, 1)">×</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-4 max-w-xs ms-auto space-y-1 text-sm">
          <div class="flex justify-between text-gray-500">
            <span>Parts Subtotal</span><span>{{ money(previewGrossSubtotal) }}</span>
          </div>
          <div v-if="previewLineDiscountTotal > 0" class="flex justify-between text-red-600">
            <span>خصم البنود</span><span>− {{ money(previewLineDiscountTotal) }}</span>
          </div>
          <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ money(previewSubtotal) }}</span></div>
          <div v-if="previewGlobalDiscount > 0" class="flex justify-between text-red-600">
            <span>خصم كلي{{ form.discount_type === 'percent' ? ` (${form.discount_value}%)` : '' }}</span>
            <span>− {{ money(previewGlobalDiscount) }}</span>
          </div>
          <div class="flex justify-between"><span class="text-gray-500">TAX {{ form.tax_percent }}%</span><span>{{ money(previewTax) }}</span></div>
          <div class="flex justify-between"><span class="text-gray-500">Withholding {{ form.withholding_tax_percent }}%</span><span>{{ money(previewWithholding) }}</span></div>
          <div class="flex justify-between font-bold text-blue-700 text-base border-t pt-2">
            <span>Total</span><span>{{ money(previewTotal) }}</span>
          </div>
        </div>
      </div>

      <!-- Linked invoice (one per quotation) -->
      <div v-if="!isNew" class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="font-bold text-gray-900 mb-2">الفاتورة</h2>
        <div v-if="invoices.length === 0" class="text-gray-400 text-sm">لم تُحوَّل لفاتورة بعد — العرض يتحول لفاتورة واحدةحدة للمشروع.</div>
        <div v-else class="flex items-center justify-between gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
          <div>
            <p class="font-mono text-sm font-medium">{{ invoices[0].number }}</p>
            <p class="text-xs text-gray-500">{{ invoices[0].date }} · {{ money(invoices[0].total || invoices[0].amount, invoices[0].currency) }}</p>
          </div>
          <router-link :to="`/admin/invoices/${invoices[0].id}`" class="text-blue-600 text-sm font-medium">عرض ←</router-link>
        </div>
      </div>
    </template>

    <Teleport to="body">
      <div v-if="showInvoiceModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" dir="rtl">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-1">تحويل عرض السعر لفاتورة</h3>
          <p class="text-sm text-gray-500 mb-4">
            سيتم إنشاء فاتورة واحدةحدة بإجمالي <strong>{{ money(previewTotal || savedTotal) }}</strong> مرتبطة بنفس المشروع.
          </p>
          <div class="space-y-3 mb-5">
            <div>
              <label class="block text-xs text-gray-500 mb-1">تاريخ الفاتورة</label>
              <input v-model="invoiceForm.date" type="date" class="sf-field" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">ملاحظات</label>
              <textarea v-model="invoiceForm.notes" rows="2" class="sf-field"></textarea>
            </div>
          </div>
          <div class="flex gap-3">
            <button type="button" @click="showInvoiceModal = false" class="flex-1 border py-2.5 rounded-lg text-sm">إلغاء</button>
            <button type="button" @click="createInvoice" :disabled="invoiceSaving" class="flex-1 bg-amber-500 text-white py-2.5 rounded-lg text-sm font-medium disabled:opacity-60">
              {{ invoiceSaving ? '...' : 'تحويل لفاتورة' }}
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
import { mediaUrl, handleMediaError } from '@/lib/media'
import { productDetailForQuote } from '@/lib/productDescription'
import {
  productCode,
  filterProducts as filterProductList,
  productDisplayName,
  productEnglishSubtitle,
  type SearchableProduct,
} from '@/lib/productSearch'
import { exportInvoicePdf, exportQuotationPdf } from '@/lib/financePdf'
import { allocateGlobalDiscount, computeGlobalDiscount } from '@/lib/quotationDiscount'

interface LineItem {
  code: string
  description: string
  quantity: number
  rate: number
  product_id: number
  discount_type: '' | 'percent' | 'fixed'
  discount_value: number | null
}

interface SectionBlock {
  title: string
  items: LineItem[]
  productQuery: string
  pickerOpen: boolean
}

interface ProductOption extends SearchableProduct {
  image?: string | null
  price?: string | number
  price_number?: number | string | null
}

interface InvoiceRow {
  id: number
  number: string
  date: string
  amount: number
  percent: number | null
  currency: string
  status: string
  total: number
}

const route = useRoute()
const router = useRouter()
const isNew = computed(() => route.params.id === 'new' || !route.params.id)

const loading = ref(!isNew.value)
const saving = ref(false)
const invoices = ref<InvoiceRow[]>([])
const savedTotal = ref(0)
const invoiced = ref(0)
const remaining = ref(0)

const products = ref<ProductOption[]>([])

interface CustomerOption { id: number; name: string; phone?: string }
interface ProjectOption { id: number; title: string; title_ar?: string; customer_id?: number | null; customer?: { name: string } }

const customers = ref<CustomerOption[]>([])
const projects = ref<ProjectOption[]>([])
const customerQuery = ref('')
const customerOpen = ref(false)
const customerWrap = ref<HTMLElement | null>(null)
const projectQuery = ref('')
const projectOpen = ref(false)
const projectWrap = ref<HTMLElement | null>(null)

const today = () => new Date().toISOString().slice(0, 10)

const form = ref({
  number: '',
  date: today(),
  customer_id: null as number | null,
  project_id: null as number | null,
  client_name: '',
  status: 'draft',
  currency: 'AED',
  tax_percent: 0,
  withholding_tax_percent: 0,
  discount_type: '' as '' | 'percent' | 'fixed',
  discount_value: null as number | null,
  comments: '',
})

const sections = ref<SectionBlock[]>([])
const activeSection = ref(0)

const showInvoiceModal = ref(false)
const invoiceSaving = ref(false)
const invoiceForm = ref({
  date: today(),
  notes: '',
})

const productPrice = (p: ProductOption) => {
  const n = Number(p.price_number)
  if (!Number.isNaN(n) && n > 0) return n
  const raw = String(p.price ?? '').replace(/[^\d.]/g, '')
  return Number(raw) || 0
}

const productDesc = (p: ProductOption) => productDetailForQuote(p.description_ar, p.description)

const filteredProductsForSection = (sec: SectionBlock) =>
  filterProductList(products.value, sec.productQuery)

const sectionTitleLabel = (sec: SectionBlock, sIdx: number) =>
  sec.title.trim() || `مجموعة ${sIdx + 1}`

const itemImage = (item: LineItem) => {
  return products.value.find((p) => p.id === item.product_id)?.image || null
}

const lineFromProduct = (p: ProductOption): LineItem => {
  const title = (p.name_ar || p.name || '').trim()
  const detail = productDesc(p)
  return {
    code: productCode(p).slice(0, 100),
    description: detail ? `${title}\n${detail}` : title,
    quantity: 1,
    rate: productPrice(p),
    product_id: p.id,
    discount_type: '',
    discount_value: null,
  }
}

const closeSectionPicker = (sec: SectionBlock) => {
  sec.pickerOpen = false
}

const openSectionPicker = (sIdx: number) => {
  activeSection.value = sIdx
  sections.value.forEach((sec, i) => {
    sec.pickerOpen = i === sIdx
  })
  setTimeout(() => {
    document.getElementById(`section-search-${sIdx}`)?.focus()
  }, 0)
}

const addSection = () => {
  sections.value.push({ title: '', items: [], productQuery: '', pickerOpen: false })
  openSectionPicker(sections.value.length - 1)
}

const removeSection = (idx: number) => {
  sections.value.splice(idx, 1)
  if (activeSection.value >= sections.value.length) {
    activeSection.value = Math.max(0, sections.value.length - 1)
  }
}

const addProduct = (p: ProductOption, sIdx: number) => {
  if (!sections.value.length) addSection()
  const sec = sections.value[sIdx]
  if (!sec) return
  const existing = sec.items.find((i) => i.product_id === p.id)
  if (existing) {
    existing.quantity = Number(existing.quantity || 0) + 1
  } else {
    sec.items.push(lineFromProduct(p))
  }
  sec.productQuery = ''
  closeSectionPicker(sec)
}

const pickFirstFiltered = (sec: SectionBlock, sIdx: number) => {
  const first = filteredProductsForSection(sec)[0]
  if (first) addProduct(first, sIdx)
}

const flattenItemsForSave = () => {
  const out: Array<Record<string, unknown>> = []
  for (const sec of sections.value) {
    const title = sec.title.trim()
    if (!title && !sec.items.length) continue
    if (!title) {
      alert('أدخل عنواناً لكل مجموعة فيها منتجات')
      return null
    }
    out.push({ is_section: true, section_title: title })
    for (const item of sec.items) {
      if (!item.product_id) continue
      out.push({
        is_section: false,
        product_id: item.product_id,
        quantity: item.quantity,
        rate: item.rate,
        discount_type: item.discount_type || null,
        discount_value: item.discount_type ? item.discount_value : null,
      })
    }
  }
  return out
}

const hydrateSectionsFromItems = (raw: any[]) => {
  const blocks: SectionBlock[] = []
  let current: SectionBlock | null = null
  for (const row of raw || []) {
    if (row.is_section) {
      current = { title: row.description || row.section_title || '', items: [], productQuery: '', pickerOpen: false }
      blocks.push(current)
      continue
    }
    if (!current) {
      current = { title: 'بنود عامة', items: [], productQuery: '', pickerOpen: false }
      blocks.push(current)
    }
    if (!row.product_id) continue
    current.items.push({
      code: row.code || row.product?.brand || `P${row.product_id}`,
      description: row.description || row.product?.name_ar || row.product?.name || '',
      quantity: Number(row.quantity || 1),
      rate: Number(row.rate || 0),
      product_id: Number(row.product_id),
      discount_type: row.discount_type === 'percent' || row.discount_type === 'fixed' ? row.discount_type : '',
      discount_value: row.discount_value != null ? Number(row.discount_value) : null,
    })
  }
  sections.value = blocks
  activeSection.value = 0
}

const selectedCustomer = computed(() => customers.value.find((c) => c.id === form.value.customer_id) || null)
const selectedProject = computed(() => projects.value.find((p) => p.id === form.value.project_id) || null)

const filteredCustomers = computed(() => {
  const q = customerQuery.value.trim().toLowerCase()
  const list = customers.value
  if (!q) return list.slice(0, 30)
  return list.filter((c) => c.name.toLowerCase().includes(q) || (c.phone || '').includes(q)).slice(0, 30)
})

const filteredProjects = computed(() => {
  const q = projectQuery.value.trim().toLowerCase()
  let list = projects.value
  if (form.value.customer_id) {
    list = list.filter((p) => p.customer_id === form.value.customer_id)
  }
  if (!q) return list.slice(0, 30)
  return list.filter((p) =>
    (p.title_ar || '').toLowerCase().includes(q) ||
    p.title.toLowerCase().includes(q) ||
    (p.customer?.name || '').toLowerCase().includes(q)
  ).slice(0, 30)
})

const selectCustomer = (c: CustomerOption) => {
  form.value.customer_id = c.id
  form.value.client_name = c.name
  customerQuery.value = ''
  customerOpen.value = false
  if (form.value.project_id) {
    const proj = projects.value.find((p) => p.id === form.value.project_id)
    if (proj && proj.customer_id !== c.id) form.value.project_id = null
  }
}
const clearCustomer = () => {
  form.value.customer_id = null
}
const selectProject = (p: ProjectOption) => {
  form.value.project_id = p.id
  if (p.customer_id) {
    form.value.customer_id = p.customer_id
    const cust = customers.value.find((c) => c.id === p.customer_id)
    if (cust) form.value.client_name = cust.name
  }
  projectQuery.value = ''
  projectOpen.value = false
}
const clearProject = () => {
  form.value.project_id = null
}

const onDocClick = (e: MouseEvent) => {
  const target = e.target as Element | null
  if (!target?.closest?.('.section-product-picker')) {
    sections.value.forEach((sec) => {
      sec.pickerOpen = false
    })
  }
  if (!customerWrap.value?.contains(e.target as Node)) customerOpen.value = false
  if (!projectWrap.value?.contains(e.target as Node)) projectOpen.value = false
}

const loadCustomers = async () => {
  try {
    const res = await api.get('/admin/customers')
    customers.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
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

const loadProducts = async () => {
  try {
    const res = await api.get('/products')
    products.value = Array.isArray(res.data?.products) ? res.data.products : []
  } catch (e) {
    console.error(e)
  }
}

const lineSubtotal = (item: LineItem) => Number(item.quantity || 0) * Number(item.rate || 0)

const lineDiscountAmount = (item: LineItem) => {
  const sub = lineSubtotal(item)
  const val = Number(item.discount_value || 0)
  if (!item.discount_type || !val) return 0
  if (item.discount_type === 'percent') return Math.min(sub, sub * Math.min(100, val) / 100)
  return Math.min(sub, val)
}

const lineAmount = (item: LineItem) => Math.max(0, lineSubtotal(item) - lineDiscountAmount(item))

const discountLabel = (item: LineItem) => {
  const amt = lineDiscountAmount(item)
  if (!amt) return ''
  if (item.discount_type === 'percent') return `${Number(item.discount_value || 0)}%`
  return money(amt)
}

const onDiscountTypeChange = (item: LineItem) => {
  if (!item.discount_type) item.discount_value = null
}

const onGlobalDiscountTypeChange = () => {
  if (!form.value.discount_type) form.value.discount_value = null
}

const previewLineDiscountTotal = computed(() =>
  sections.value.reduce((sum, sec) => sum + sec.items.reduce((s, i) => s + lineDiscountAmount(i), 0), 0)
)
const previewSubtotal = computed(() =>
  sections.value.reduce((sum, sec) => sum + sec.items.reduce((s, i) => s + lineAmount(i), 0), 0)
)
const previewGrossSubtotal = computed(() => previewSubtotal.value + previewLineDiscountTotal.value)
const previewGlobalDiscount = computed(() =>
  computeGlobalDiscount(previewSubtotal.value, form.value.discount_type, form.value.discount_value)
)

const previewGlobalShareMap = computed(() => {
  const keys: string[] = []
  const amounts: number[] = []
  sections.value.forEach((sec, sIdx) => {
    sec.items.forEach((item, iIdx) => {
      keys.push(`${sIdx}-${iIdx}`)
      amounts.push(lineAmount(item))
    })
  })
  const shares = allocateGlobalDiscount(previewGlobalDiscount.value, amounts)
  const map = new Map<string, number>()
  keys.forEach((key, i) => map.set(key, shares[i] ?? 0))
  return map
})

const globalDiscountRateLabel = computed(() => {
  if (form.value.discount_type === 'percent' && form.value.discount_value) {
    return `${Number(form.value.discount_value)}%`
  }
  if (form.value.discount_type === 'fixed' && previewGlobalDiscount.value > 0) {
    return 'حصة من الكلي'
  }
  return ''
})

const lineGlobalShare = (sIdx: number, idx: number) =>
  previewGlobalShareMap.value.get(`${sIdx}-${idx}`) ?? 0

const lineFinalAmount = (item: LineItem, sIdx: number, idx: number) =>
  Math.max(0, lineAmount(item) - lineGlobalShare(sIdx, idx))

const previewNetSubtotal = computed(() => Math.max(0, previewSubtotal.value - previewGlobalDiscount.value))
const previewTax = computed(() => previewNetSubtotal.value * (Number(form.value.tax_percent) || 0) / 100)
const previewWithholding = computed(() => previewNetSubtotal.value * (Number(form.value.withholding_tax_percent) || 0) / 100)
const previewTotal = computed(() => previewNetSubtotal.value + previewTax.value - previewWithholding.value)

const money = (n: number, currency = form.value.currency || 'AED') =>
  `${currency} ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const load = async () => {
  if (isNew.value) {
    if (!sections.value.length) addSection()
    return
  }
  loading.value = true
  try {
    const res = await api.get(`/admin/quotations/${route.params.id}`)
    const q = res.data
    form.value = {
      number: q.number || '',
      date: (q.date || '').toString().slice(0, 10),
      customer_id: q.customer_id ?? null,
      project_id: q.project_id ?? null,
      client_name: q.client_name || '',
      status: q.status || 'draft',
      currency: q.currency || 'AED',
      tax_percent: Number(q.tax_percent || 0),
      withholding_tax_percent: Number(q.withholding_tax_percent || 0),
      discount_type: q.discount_type === 'percent' || q.discount_type === 'fixed' ? q.discount_type : '',
      discount_value: q.discount_value != null ? Number(q.discount_value) : null,
      comments: q.comments || '',
    }
    hydrateSectionsFromItems(q.items || [])
    invoices.value = q.invoices || []
    savedTotal.value = Number(q.total || 0)
    invoiced.value = Number(q.invoiced_amount || 0)
    remaining.value = Number(q.remaining_amount || 0)
    invoiceForm.value.notes = `Invoice from ${q.number}`
  } catch {
    alert('تعذر تحميل عرض السعر')
    router.push('/admin/quotations')
  } finally {
    loading.value = false
  }
}

const save = async () => {
  if (!form.value.client_name.trim()) {
    alert('أدخل اسم العميل')
    return
  }
  const items = flattenItemsForSave()
  if (!items) return
  if (!items.some((i) => !i.is_section)) {
    alert('أضف منتجاً واحداً على الأقل تحت العناوين')
    return
  }
  saving.value = true
  try {
    const payload = {
      number: form.value.number || null,
      date: form.value.date,
      customer_id: form.value.customer_id,
      project_id: form.value.project_id,
      client_name: form.value.client_name,
      status: form.value.status,
      currency: form.value.currency,
      tax_percent: form.value.tax_percent,
      withholding_tax_percent: form.value.withholding_tax_percent,
      discount_type: form.value.discount_type || null,
      discount_value: form.value.discount_type ? form.value.discount_value : null,
      comments: form.value.comments || null,
      items,
    }
    if (isNew.value) {
      const res = await api.post('/admin/quotations', payload)
      router.replace(`/admin/quotations/${res.data.id}`)
    } else {
      const res = await api.put(`/admin/quotations/${route.params.id}`, payload)
      invoices.value = res.data.invoices || invoices.value
      savedTotal.value = Number(res.data.total || 0)
      invoiced.value = Number(res.data.invoiced_amount || 0)
      remaining.value = Number(res.data.remaining_amount || 0)
      form.value.number = res.data.number
      hydrateSectionsFromItems(res.data.items || [])
      alert('تم الحفظ')
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'تعذر الحفظ')
  } finally {
    saving.value = false
  }
}

const createInvoice = async () => {
  invoiceSaving.value = true
  try {
    const body = {
      date: invoiceForm.value.date,
      notes: invoiceForm.value.notes || null,
    }
    const res = await api.post(`/admin/quotations/${route.params.id}/invoices`, body)
    invoices.value = [res.data, ...invoices.value]
    await load()
    showInvoiceModal.value = false
    alert(`تم إنشاء الفاتورة ${res.data.number}`)
    router.push(`/admin/invoices/${res.data.id}`)
  } catch (err: any) {
    alert(err.response?.data?.message || 'تعذر إنشاء الفاتورة')
  } finally {
    invoiceSaving.value = false
  }
}

const downloadPdf = async () => {
  try {
    await exportQuotationPdf(route.params.id as string, form.value.number)
  } catch {
    alert('تعذر تصدير PDF')
  }
}

const downloadInvoicePdf = async (inv: InvoiceRow) => {
  try {
    await exportInvoicePdf(inv.id, inv.number)
  } catch {
    alert('تعذر تصدير فاتورة PDF')
  }
}

onMounted(async () => {
  document.addEventListener('click', onDocClick)
  await Promise.all([loadProducts(), loadCustomers(), loadProjects(), load()])
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
})
</script>


