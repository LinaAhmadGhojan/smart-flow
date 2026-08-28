<template>
  <div>
    <!-- Header -->
    <div class="sf-page-header">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">إدارة العروض | Manage Offers</h1>
        <p class="text-gray-600 mt-1">إضافة وتعديل وحذف العروض الخاصة</p>
      </div>
      <button
        @click="showForm = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة عرض جديد | Add Offer
      </button>
    </div>

    <!-- Main Content -->
    <div>
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p class="mt-4 text-gray-600">جاري التحميل...</p>
        </div>
      </div>

      <!-- Offers Table -->
      <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="offers.length > 0" class="sf-table-wrap">
          <table class="sf-table">
            <thead class="bg-gray-100 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">المنتج</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">نسبة الخصم</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">تاريخ البداية</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">تاريخ النهاية</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">الحالة</th>
                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">الإجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="offer in offers" :key="offer.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img
                      :src="offer.product.image || '/placeholder-product.jpg'"
                      :alt="offer.product.name"
                      class="w-12 h-12 object-cover rounded"
                    />
                    <div>
                      <p class="font-medium text-gray-900">{{ offer.product.name }}</p>
                      <p class="text-sm text-gray-600">{{ offer.product.name_ar }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-bold text-red-600">{{ offer.discount_percentage }}%</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ offer.start_date ? new Date(offer.start_date).toLocaleDateString('ar-AE') : 'بدون تاريخ' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ offer.end_date ? new Date(offer.end_date).toLocaleDateString('ar-AE') : 'بدون تاريخ' }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                      offer.is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ offer.is_active ? 'مفعل | Active' : 'معطل | Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <button
                      type="button"
                      @click="editOffer(offer)"
                      class="text-blue-500 hover:text-blue-700 transition-colors"
                      title="تعديل"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button
                      type="button"
                      @click="confirmDelete(offer)"
                      class="text-red-500 hover:text-red-700 transition-colors"
                      title="حذف"
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

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"/>
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 mb-1">لا توجد عروض</h3>
          <p class="text-gray-600 mb-6">ابدأ بإضافة عرض جديد لمنتجاتك</p>
          <button
            @click="showForm = true"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
          >
            إضافة عرض جديد
          </button>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="sf-modal-backdrop" dir="rtl">
      <div class="sf-modal-panel max-w-3xl">
        <!-- Form Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 -mx-5 sm:-mx-6 md:-mx-8 px-5 sm:px-6 md:px-8 py-5 flex items-center justify-between mb-6 z-10">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ editingOffer ? 'تعديل عرض | Edit Offer' : 'إضافة عرض جديد | Add New Offer' }}
          </h2>
          <button
            type="button"
            @click="closeForm"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="saveOffer" class="space-y-5">
          <!-- Product search picker -->
          <div>
            <label class="sf-label">
              المنتج | Product <span class="text-red-500">*</span>
            </label>

            <div
              v-if="selectedProduct"
              class="mb-3 flex items-center gap-3 p-3 bg-blue-50 rounded-xl border border-blue-100"
            >
              <div class="w-14 h-14 rounded-lg overflow-hidden bg-white border border-gray-200 flex-shrink-0">
                <img
                  :src="mediaUrl(selectedProduct.image, '/logo.jpeg')"
                  :alt="selectedProduct.name_ar || selectedProduct.name"
                  class="w-full h-full object-cover"
                  @error="handleMediaError"
                />
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-mono text-[11px] text-gray-500 bg-white px-1.5 py-0.5 rounded border">{{ productCode(selectedProduct) }}</span>
                  <span class="font-medium text-gray-900 truncate">{{ selectedProduct.name_ar || selectedProduct.name }}</span>
                </div>
                <p v-if="selectedProduct.name_ar && selectedProduct.name" class="text-xs text-gray-500 truncate mt-0.5">{{ selectedProduct.name }}</p>
              </div>
              <button
                type="button"
                class="text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded-lg hover:bg-red-50 flex-shrink-0"
                @click="clearSelectedProduct"
              >
                إزالة
              </button>
            </div>

            <div class="relative" ref="productSearchWrap">
              <div class="relative">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
                </svg>
                <input
                  v-model="productQuery"
                  type="text"
                  class="sf-field !pr-9"
                  placeholder="ابحث بالاسم (عربي / English) أو الكود أو الوصف..."
                  autocomplete="off"
                  @focus="productDropdownOpen = true"
                  @input="productDropdownOpen = true"
                  @keydown.escape="closeProductPicker"
                  @keydown.enter.prevent="pickFirstFiltered"
                />
              </div>

              <div
                v-if="productDropdownOpen"
                class="absolute z-40 mt-1 w-full max-h-72 overflow-auto bg-white border border-blue-200 rounded-xl shadow-xl"
              >
                <div class="sticky top-0 bg-blue-50 border-b px-3 py-2 text-xs font-medium text-blue-800 flex items-center justify-between">
                  <span>نتائج البحث — اضغط للاختيار</span>
                  <button type="button" class="text-blue-600 hover:text-blue-900" @click="closeProductPicker">إغلاق</button>
                </div>
                <button
                  v-for="p in filteredProducts"
                  :key="'offer-pick-' + p.id"
                  type="button"
                  class="w-full text-right px-3 py-2.5 hover:bg-blue-50 border-b border-gray-50 last:border-0 flex items-center gap-3"
                  :class="formData.product_id === p.id ? 'bg-blue-50/80' : ''"
                  @click="selectProduct(p)"
                >
                  <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                    <img
                      :src="mediaUrl(p.image, '/logo.jpeg')"
                      :alt="p.name_ar || p.name"
                      class="w-full h-full object-cover"
                      @error="handleMediaError"
                    />
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="font-mono text-[11px] text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ productCode(p) }}</span>
                      <span class="text-sm font-medium text-gray-900 truncate">{{ productDisplayName(p) }}</span>
                    </div>
                    <p v-if="productEnglishSubtitle(p)" class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ productEnglishSubtitle(p) }}</p>
                    <p v-else-if="productDesc(p)" class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ productDesc(p) }}</p>
                  </div>
                  <span class="text-sm font-semibold text-blue-700 whitespace-nowrap flex-shrink-0">{{ money(productPrice(p)) }}</span>
                </button>
                <p v-if="!filteredProducts.length" class="px-3 py-4 text-sm text-gray-400 text-center">لا توجد منتجات مطابقة</p>
              </div>
            </div>
          </div>

          <!-- Discount Percentage -->
          <div>
            <label class="sf-label">
              نسبة الخصم | Discount Percentage <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="formData.discount_percentage"
              type="number"
              min="0"
              max="100"
              required
              placeholder="مثلاً: 20"
              class="sf-field"
            />
          </div>

          <!-- Discount Price (Optional) -->
          <div>
            <label class="sf-label">
              سعر الخصم (اختياري) | Discount Price
            </label>
            <input
              v-model.number="formData.discount_price"
              type="number"
              min="0"
              placeholder="اتركها فارغة لحساب تلقائي"
              class="sf-field"
            />
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <!-- Start Date -->
            <div>
              <label class="sf-label">
                تاريخ البداية | Start Date
              </label>
              <input
                v-model="formData.start_date"
                type="datetime-local"
                class="sf-field"
              />
            </div>

            <!-- End Date -->
            <div>
              <label class="sf-label">
                تاريخ النهاية | End Date
              </label>
              <input
                v-model="formData.end_date"
                type="datetime-local"
                class="sf-field"
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="sf-label">
              وصف العرض | Offer Description
            </label>
            <textarea
              v-model="formData.offer_description"
              rows="3"
              class="sf-field"
              placeholder="وصف العرض بالإنجليزية"
            ></textarea>
          </div>

          <!-- Description AR -->
          <div>
            <label class="sf-label">
              وصف العرض | Offer Description AR
            </label>
            <textarea
              v-model="formData.offer_description_ar"
              rows="3"
              class="sf-field"
              placeholder="وصف العرض بالعربية"
            ></textarea>
          </div>

          <!-- Is Active -->
          <div class="flex items-center gap-3">
            <input
              v-model="formData.is_active"
              type="checkbox"
              id="is_active"
              class="w-4 h-4 text-blue-600 rounded focus:ring-2"
            />
            <label for="is_active" class="text-sm font-medium text-gray-900">
              مفعل | Active
            </label>
          </div>

          <!-- Buttons -->
          <div class="sf-actions border-t border-gray-200 pt-4">
            <button
              type="submit"
              :disabled="saving"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition-colors"
            >
              {{ saving ? 'جاري الحفظ...' : 'حفظ | Save' }}
            </button>
            <button
              type="button"
              @click="closeForm"
              class="bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-3 rounded-lg font-medium transition-colors"
            >
              إلغاء | Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف العرض؟</p>
          <p class="text-gray-500 text-sm mb-6">
            {{ deleteTarget.product?.name_ar || deleteTarget.product?.name || ('#' + deleteTarget.id) }}
          </p>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { mediaUrl, handleMediaError } from '@/lib/media'
import {
  productCode,
  filterProducts as filterProductList,
  productDisplayName,
  productEnglishSubtitle,
  type SearchableProduct,
} from '@/lib/productSearch'

interface Product extends SearchableProduct {
  price?: string | null
  price_number?: number | null
  image: string
}

interface Offer {
  id: number
  product_id: number
  discount_percentage: number
  discount_price: number | null
  offer_description: string
  offer_description_ar: string
  start_date: string | null
  end_date: string | null
  is_active: boolean
  product: Product
}

interface FormData {
  product_id: number | null
  discount_percentage: number | null
  discount_price: number | null
  offer_description: string
  offer_description_ar: string
  start_date: string
  end_date: string
  is_active: boolean
}

const offers = ref<Offer[]>([])
const products = ref<Product[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingOffer = ref<Offer | null>(null)
const deleteTarget = ref<Offer | null>(null)
const deleteLoading = ref(false)

const productQuery = ref('')
const productDropdownOpen = ref(false)
const productSearchWrap = ref<HTMLElement | null>(null)

const formData = ref<FormData>({
  product_id: null,
  discount_percentage: null,
  discount_price: null,
  offer_description: '',
  offer_description_ar: '',
  start_date: '',
  end_date: '',
  is_active: true,
})

const selectedProduct = computed(() => {
  if (!formData.value.product_id) return null
  return products.value.find((p) => p.id === formData.value.product_id) || null
})

const productPrice = (p: Product) => {
  const n = Number(p.price_number)
  if (!Number.isNaN(n) && n > 0) return n
  const raw = String(p.price ?? '').replace(/[^\d.]/g, '')
  return Number(raw) || 0
}

const productDesc = (p: Product) => (p.description_ar || p.description || '').trim()

const money = (n: number) =>
  `AED ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const filteredProducts = computed(() => filterProductList(products.value, productQuery.value))

const closeProductPicker = () => {
  productDropdownOpen.value = false
}

const selectProduct = (p: Product) => {
  formData.value.product_id = p.id
  productQuery.value = ''
  closeProductPicker()
}

const clearSelectedProduct = () => {
  formData.value.product_id = null
  productQuery.value = ''
}

const pickFirstFiltered = () => {
  const first = filteredProducts.value[0]
  if (first) selectProduct(first)
}

const onDocClick = (e: MouseEvent) => {
  if (!productSearchWrap.value?.contains(e.target as Node)) {
    closeProductPicker()
  }
}

const resetForm = () => {
  formData.value = {
    product_id: null,
    discount_percentage: null,
    discount_price: null,
    offer_description: '',
    offer_description_ar: '',
    start_date: '',
    end_date: '',
    is_active: true,
  }
  editingOffer.value = null
  productQuery.value = ''
  closeProductPicker()
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const editOffer = (offer: Offer) => {
  editingOffer.value = offer
  formData.value = {
    product_id: offer.product_id,
    discount_percentage: offer.discount_percentage,
    discount_price: offer.discount_price,
    offer_description: offer.offer_description,
    offer_description_ar: offer.offer_description_ar,
    start_date: offer.start_date ? new Date(offer.start_date).toISOString().slice(0, 16) : '',
    end_date: offer.end_date ? new Date(offer.end_date).toISOString().slice(0, 16) : '',
    is_active: offer.is_active,
  }
  productQuery.value = ''
  closeProductPicker()
  showForm.value = true
}

const saveOffer = async () => {
  if (!formData.value.product_id || !formData.value.discount_percentage) {
    alert('الرجاء ملء جميع الحقول المطلوبة')
    return
  }

  saving.value = true
  try {
    const url = editingOffer.value 
      ? `/api/offers/${editingOffer.value.id}`
      : '/api/offers'
    
    const method = editingOffer.value ? 'PUT' : 'POST'
    
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken
    }
    
    const token = sessionStorage.getItem('authToken')
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    const response = await fetch(url, {
      method,
      headers,
      credentials: 'same-origin',
      body: JSON.stringify(formData.value)
    })

    if (response.ok) {
      closeForm()
      await loadOffers()
      alert(editingOffer.value ? 'تم تحديث العرض بنجاح' : 'تم إضافة العرض بنجاح')
    } else {
      const error = await response.text()
      alert('حدث خطأ: ' + error)
    }
  } catch (error) {
    console.error('Error:', error)
    alert('حدث خطأ في الاتصال')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (offer: Offer) => {
  deleteTarget.value = offer
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    const headers: HeadersInit = {
      'Accept': 'application/json',
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken
    }

    const token = sessionStorage.getItem('authToken')
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    const response = await fetch(`/api/offers/${deleteTarget.value.id}`, {
      method: 'DELETE',
      headers,
      credentials: 'same-origin'
    })

    if (response.ok) {
      await loadOffers()
      deleteTarget.value = null
    } else {
      alert('فشل حذف العرض')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('حدث خطأ في الاتصال')
  } finally {
    deleteLoading.value = false
  }
}

const loadOffers = async () => {
  try {
    const response = await fetch('/api/offers')
    const data = await response.json()
    offers.value = data.offers || []
  } catch (error) {
    console.error('Error loading offers:', error)
  }
}

const loadProducts = async () => {
  try {
    const response = await fetch('/api/products')
    const data = await response.json()
    products.value = data.products || []
  } catch (error) {
    console.error('Error loading products:', error)
  }
}

onMounted(async () => {
  document.addEventListener('click', onDocClick)
  loading.value = true
  await Promise.all([loadOffers(), loadProducts()])
  loading.value = false
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
})
</script>

