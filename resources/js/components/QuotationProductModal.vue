<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[60] bg-black/55 flex items-center justify-center p-3 sm:p-6"
      dir="rtl"
      @click.self="emit('cancel')"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[94vh] flex flex-col overflow-hidden">
        <div class="shrink-0 border-b px-5 sm:px-8 py-4 flex items-center justify-between gap-3 bg-white">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              {{ productId ? 'تعديل منتج' : 'إضافة منتج' }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">نفس حقول صفحة المنتجات في لوحة الإدارة</p>
          </div>
          <button type="button" class="text-gray-400 hover:text-gray-700 text-3xl leading-none px-2" @click="emit('cancel')">×</button>
        </div>

        <div class="relative flex-1 overflow-y-auto">
          <div
            v-if="pageLoading"
            class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-3 bg-white/85 backdrop-blur-[1px]"
          >
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
            <p class="text-sm text-gray-600">جاري تحميل البيانات...</p>
          </div>

          <form class="p-5 sm:p-8 space-y-6" @submit.prevent="submit">
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ error }}</div>

            <div class="sf-form-grid">
              <div class="min-w-0">
                <label class="sf-label">Name (English)</label>
                <input v-model="form.name" type="text" required class="sf-field" />
              </div>
              <div class="min-w-0">
                <label class="sf-label">الاسم (عربي)</label>
                <input v-model="form.name_ar" type="text" required class="sf-field" dir="rtl" />
              </div>
            </div>

            <div class="sf-form-grid">
              <div class="min-w-0">
                <label class="sf-label">الكود / Brand</label>
                <input v-model="form.brand" type="text" class="sf-field" dir="ltr" placeholder="CAM-001" />
              </div>
              <div class="min-w-0">
                <label class="sf-label">Price (AED)</label>
                <input v-model.number="form.price" type="number" step="0.01" required class="sf-field" />
              </div>
            </div>

            <div class="sf-form-grid">
              <div class="min-w-0">
                <label class="sf-label">الفئة</label>
                <select v-model="form.category_id" required class="sf-field">
                  <option value="">Select Category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }} | {{ cat.name_ar }}
                  </option>
                </select>
              </div>
              <div class="min-w-0">
                <label class="sf-label">المجموعة</label>
                <select v-model="form.group_id" class="sf-field">
                  <option value="">بدون مجموعة</option>
                  <option v-for="group in groups" :key="group.id" :value="group.id">
                    {{ group.name }} | {{ group.name_ar }}
                  </option>
                </select>
              </div>
            </div>

            <div class="sf-form-grid">
              <div class="min-w-0">
                <label class="sf-label">Description (English)</label>
                <textarea v-model="form.description" rows="5" required class="sf-field"></textarea>
              </div>
              <div class="min-w-0">
                <label class="sf-label">الوصف (عربي)</label>
                <textarea v-model="form.description_ar" rows="5" required class="sf-field" dir="rtl"></textarea>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div class="min-w-0 space-y-4 lg:col-span-2">
                <div class="min-w-0 flex items-end">
                  <label class="inline-flex items-center gap-2 pb-2.5 cursor-pointer">
                    <input v-model="form.in_stock" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                    <span class="text-sm text-gray-700">متوفر في المخزون</span>
                  </label>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 bg-gray-50">
                  <input
                    v-model="form.is_visible"
                    type="checkbox"
                    id="qp-modal-is-visible"
                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded"
                  />
                  <div>
                    <label for="qp-modal-is-visible" class="block text-sm font-medium text-gray-800">إظهار للعملاء</label>
                    <p class="text-xs text-gray-500 mt-1">
                      إذا ألغيت التحديد، المنتج يظهر في لوحة الإدارة فقط ولا يظهر للزوار في الموقع.
                    </p>
                  </div>
                </div>

                <div>
                  <label class="sf-label">رسالة واتساب</label>
                  <textarea v-model="form.whatsapp_message" rows="3" placeholder="Custom message for WhatsApp..." class="sf-field" />
                  <p class="text-sm text-gray-500 mt-1">Optional custom message when sharing via WhatsApp</p>
                </div>
              </div>

              <div class="min-w-0">
                <label class="sf-label">صورة المنتج</label>
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center">
                  <div v-if="imagePreview" class="mb-3 flex justify-center">
                    <img :src="imagePreview" alt="Preview" class="h-40 w-40 object-cover rounded-lg border border-gray-200" @error="handleMediaError" />
                  </div>
                  <div v-else class="mb-3 h-40 flex items-center justify-center text-sm text-gray-400">لا توجد صورة</div>
                  <input type="file" accept="image/*" class="sf-field" @change="onImageChange" />
                  <p class="text-xs text-gray-500 mt-2">JPG, PNG, WebP — max 4MB (auto-compressed)</p>
                </div>

                <label class="sf-label mt-4">ورقة البيانات (PDF)</label>
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4">
                  <div v-if="form.data_sheet && !removeDataSheet" class="mb-3 flex items-center justify-between gap-2 rounded-lg bg-white border border-gray-200 px-3 py-2">
                    <a :href="mediaUrl(form.data_sheet, '#')" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate">
                      عرض الملف الحالي
                    </a>
                    <button type="button" class="text-xs text-red-600 hover:text-red-700 shrink-0" @click="clearDataSheet">إزالة</button>
                  </div>
                  <p v-else-if="dataSheetFile" class="mb-2 text-sm text-emerald-700 truncate">تم اختيار: {{ dataSheetFile.name }}</p>
                  <input type="file" accept="application/pdf,.pdf" class="sf-field" @change="onDataSheetChange" />
                  <p class="text-xs text-gray-500 mt-2">PDF فقط — حتى 20MB</p>
                </div>
              </div>
            </div>

            <div>
              <label class="sf-label">الميزات</label>
              <div class="space-y-2">
                <div v-for="(feature, index) in form.features" :key="index" class="flex flex-col sm:flex-row gap-2">
                  <input v-model="form.features[index]" type="text" placeholder="Enter feature" class="sf-field flex-1" />
                  <button type="button" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm shrink-0" @click="removeFeature(index)">
                    Remove
                  </button>
                </div>
              </div>
              <button type="button" class="mt-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm" @click="addFeature">
                + Add Feature
              </button>
            </div>
          </form>
        </div>

        <div class="shrink-0 border-t bg-gray-50 px-5 sm:px-8 py-4 flex flex-col sm:flex-row gap-3">
          <button type="button" class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg" @click="emit('cancel')">
            إلغاء
          </button>
          <button
            type="button"
            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg"
            :disabled="saving || pageLoading"
            @click="submit"
          >
            {{ saving ? 'جاري الحفظ...' : (productId ? 'تحديث' : 'إنشاء') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'
import { compressImage } from '@/lib/compressImage'

export interface SavedQuotationProduct {
  id: number
  name: string
  name_ar: string
  brand?: string | null
  description?: string | null
  description_ar?: string | null
  price?: number | string
  price_number?: number | string | null
  image?: string | null
  category_id?: number
  features?: string[]
}

const props = defineProps<{
  open: boolean
  productId?: number | null
}>()

const emit = defineEmits<{
  cancel: []
  saved: [product: SavedQuotationProduct]
}>()

const categories = ref<{ id: number; name: string; name_ar?: string }[]>([])
const groups = ref<{ id: number; name: string; name_ar?: string }[]>([])
const saving = ref(false)
const pageLoading = ref(false)
const error = ref('')
const imagePreview = ref<string | null>(null)
const imageFile = ref<File | null>(null)
const dataSheetFile = ref<File | null>(null)
const removeDataSheet = ref(false)

const emptyForm = () => ({
  brand: '',
  name: '',
  name_ar: '',
  description: '',
  description_ar: '',
  price: 0,
  category_id: '' as string | number,
  group_id: '' as string | number,
  features: [] as string[],
  in_stock: true,
  is_visible: true,
  whatsapp_message: '',
  data_sheet: '',
})

const form = ref(emptyForm())

const applyProductToForm = (product: Record<string, unknown>) => {
  form.value = {
    brand: String(product.brand || ''),
    name: String(product.name || ''),
    name_ar: String(product.name_ar || ''),
    description: String(product.description || ''),
    description_ar: String(product.description_ar || product.description || ''),
    price: Number(product.price_number ?? product.price ?? 0),
    category_id: (product.category_id as number) || (product.categoryId as number) || '',
    group_id: (product.group_id as number) || '',
    features: Array.isArray(product.features) ? [...(product.features as string[])] : [],
    in_stock: product.in_stock !== false,
    is_visible: product.is_visible !== false,
    whatsapp_message: String(product.whatsapp_message || ''),
    data_sheet: String(product.data_sheet || ''),
  }
  imagePreview.value = product.image ? mediaUrl(String(product.image)) : null
  imageFile.value = null
  dataSheetFile.value = null
  removeDataSheet.value = false
}

const loadMeta = async () => {
  const [categoriesRes, groupsRes] = await Promise.all([api.get('/categories'), api.get('/groups')])
  categories.value = Array.isArray(categoriesRes.data) ? categoriesRes.data : []
  groups.value = Array.isArray(groupsRes.data) ? groupsRes.data : []
}

const loadProduct = async (id: number) => {
  const res = await api.get(`/products/${id}`)
  applyProductToForm(res.data)
}

watch(
  () => [props.open, props.productId] as const,
  async ([open, productId]) => {
    if (!open) return
    error.value = ''
    saving.value = false
    pageLoading.value = true
    try {
      await loadMeta()
      if (productId) {
        await loadProduct(productId)
      } else {
        form.value = emptyForm()
        if (categories.value.length && !form.value.category_id) {
          form.value.category_id = categories.value[0].id
        }
        imagePreview.value = null
        imageFile.value = null
        dataSheetFile.value = null
        removeDataSheet.value = false
      }
    } catch {
      error.value = 'تعذر تحميل بيانات المنتج'
    } finally {
      pageLoading.value = false
    }
  },
  { immediate: true },
)

const onImageChange = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  try {
    const compressed = await compressImage(file)
    imageFile.value = compressed
    imagePreview.value = URL.createObjectURL(compressed)
  } catch {
    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const onDataSheetChange = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
    alert('يرجى اختيار ملف PDF فقط')
    return
  }
  dataSheetFile.value = file
  removeDataSheet.value = false
}

const clearDataSheet = () => {
  dataSheetFile.value = null
  removeDataSheet.value = true
  form.value.data_sheet = ''
}

const addFeature = () => {
  form.value.features.push('')
}

const removeFeature = (index: number) => {
  form.value.features.splice(index, 1)
}

const submit = async () => {
  saving.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('name', form.value.name)
    fd.append('name_ar', form.value.name_ar)
    fd.append('description', form.value.description)
    fd.append('description_ar', form.value.description_ar)
    fd.append('price', form.value.price.toString())
    fd.append('category_id', String(form.value.category_id))
    fd.append('brand', form.value.brand || '')
    if (form.value.group_id) fd.append('group_id', String(form.value.group_id))
    fd.append('in_stock', form.value.in_stock ? '1' : '0')
    fd.append('is_visible', form.value.is_visible ? '1' : '0')
    fd.append('whatsapp_message', form.value.whatsapp_message)
    fd.append('features', JSON.stringify(form.value.features.filter(Boolean)))
    if (imageFile.value) fd.append('image', imageFile.value)
    if (dataSheetFile.value) fd.append('data_sheet', dataSheetFile.value)
    if (removeDataSheet.value && !dataSheetFile.value) fd.append('remove_data_sheet', '1')

    let res
    if (props.productId) {
      res = await api.post(`/products/${props.productId}?_method=PUT`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      res = await api.post('/products', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    const product = (res.data?.product ?? res.data) as SavedQuotationProduct
    emit('saved', product)
  } catch (err: unknown) {
    const e = err as { response?: { data?: { error?: string; message?: string } } }
    error.value = e.response?.data?.error || e.response?.data?.message || 'تعذر حفظ المنتج'
  } finally {
    saving.value = false
  }
}
</script>
