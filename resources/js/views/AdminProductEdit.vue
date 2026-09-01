<template>
  <div class="w-full min-w-0">
    <div class="sf-page-header">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ isNew ? 'إضافة منتج | Add Product' : 'تعديل منتج | Edit Product' }}
      </h1>
      <RouterLink to="/admin/products" class="text-sm text-gray-500 hover:text-blue-600">
        ← العودة للمنتجات
      </RouterLink>
    </div>

    <div v-if="success" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg mb-4">
      {{ success }}
    </div>
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
      {{ error }}
    </div>

    <div class="relative">
      <div
        v-if="pageLoading"
        class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-3 rounded-xl bg-white/80 backdrop-blur-[1px]"
      >
        <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        <p class="text-sm text-gray-600">جاري تحميل البيانات...</p>
      </div>

    <form @submit.prevent="handleSubmit" class="sf-card p-4 sm:p-6 md:p-8 space-y-6 w-full">
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
          <label class="sf-label">Category | الفئة</label>
          <select v-model="form.category_id" required class="sf-field">
            <option value="">Select Category</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }} | {{ cat.name_ar }}
            </option>
          </select>
        </div>
        <div class="min-w-0">
          <label class="sf-label">Group | المجموعة</label>
          <select v-model="form.group_id" class="sf-field">
            <option value="">No Group | بدون مجموعة</option>
            <option v-for="group in groups" :key="group.id" :value="group.id">
              {{ group.name }} | {{ group.name_ar }}
            </option>
          </select>
        </div>
      </div>

      <div class="sf-form-grid">
        <div class="min-w-0">
          <label class="sf-label">Description (English)</label>
          <textarea v-model="form.description" rows="4" required class="sf-field"></textarea>
        </div>
        <div class="min-w-0">
          <label class="sf-label">الوصف (عربي)</label>
          <textarea v-model="form.description_ar" rows="4" required class="sf-field" dir="rtl"></textarea>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="min-w-0 space-y-4 lg:col-span-2">
          <div class="sf-form-grid">
            <div class="min-w-0">
              <label class="sf-label">Price (AED)</label>
              <input v-model.number="form.price" type="number" step="0.01" required class="sf-field" />
            </div>
            <div class="min-w-0 flex items-end">
              <label class="inline-flex items-center gap-2 pb-2.5 cursor-pointer">
                <input
                  v-model="form.in_stock"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="text-sm text-gray-700">In Stock | متوفر في المخزون</span>
              </label>
            </div>
          </div>

          <div class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 bg-gray-50">
            <input
              v-model="form.is_visible"
              type="checkbox"
              id="is_visible"
              class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <div>
              <label for="is_visible" class="block text-sm font-medium text-gray-800">
                إظهار للعملاء | Visible on website
              </label>
              <p class="text-xs text-gray-500 mt-1">
                إذا ألغيت التحديد، المنتج يظهر في لوحة الإدارة فقط ولا يظهر للزوار في الموقع.
              </p>
            </div>
          </div>

          <div>
            <label class="sf-label">WhatsApp Message | رسالة واتساب</label>
            <textarea
              v-model="form.whatsapp_message"
              rows="3"
              placeholder="Custom message for WhatsApp..."
              class="sf-field"
            ></textarea>
            <p class="text-sm text-gray-500 mt-1">Optional custom message when sharing via WhatsApp</p>
          </div>
        </div>

        <div class="min-w-0">
          <label class="sf-label">Product Image | صورة المنتج</label>
          <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center">
            <div v-if="imagePreview" class="mb-3 flex justify-center">
              <img :src="imagePreview" alt="Preview" class="h-40 w-40 object-cover rounded-lg border border-gray-200" @error="handleMediaError" />
            </div>
            <div v-else class="mb-3 h-40 flex items-center justify-center text-sm text-gray-400">
              لا توجد صورة
            </div>
            <input type="file" accept="image/*" @change="handleImageUpload" class="sf-field" />
            <p class="text-xs text-gray-500 mt-2">JPG, PNG, WebP</p>
          </div>
        </div>
      </div>

      <div>
        <label class="sf-label">Features | الميزات</label>
        <div class="space-y-2">
          <div v-for="(feature, index) in form.features" :key="index" class="flex flex-col sm:flex-row gap-2">
            <input
              v-model="form.features[index]"
              type="text"
              placeholder="Enter feature"
              class="sf-field flex-1"
            />
            <button
              type="button"
              @click="removeFeature(index)"
              class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors text-sm shrink-0"
            >
              Remove
            </button>
          </div>
        </div>
        <button
          type="button"
          @click="addFeature"
          class="mt-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors text-sm"
        >
          + Add Feature
        </button>
      </div>

      <div class="sf-actions border-t pt-4">
        <RouterLink
          to="/admin/products"
          class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-center"
        >
          Cancel | إلغاء
        </RouterLink>
        <button
          type="submit"
          :disabled="loading || pageLoading"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg"
        >
          {{ loading ? 'Saving...' : (isNew ? 'Create | إنشاء' : 'Update | تحديث') }}
        </button>
        <button
          v-if="nextProduct"
          type="button"
          :disabled="loading || pageLoading"
          class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg"
          @click="goToNextProduct"
        >
          {{ pageLoading ? 'جاري التحميل...' : 'التالي' }}
        </button>
      </div>
    </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter, RouterLink, onBeforeRouteUpdate } from 'vue-router'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'
import {
  getNextProduct,
  readProductNavList,
  saveProductNavList,
  type ProductNavItem,
} from '@/lib/adminProductNav'

const route = useRoute()
const router = useRouter()

const isNew = computed(() => route.name === 'admin-product-new')
const loading = ref(false)
const pageLoading = ref(false)
const error = ref('')
const success = ref('')
const categories = ref<any[]>([])
const groups = ref<any[]>([])
const imagePreview = ref<string | null>(null)
const imageFile = ref<File | null>(null)
const productNavList = ref<ProductNavItem[]>(readProductNavList())

const nextProduct = computed(() => {
  if (isNew.value) return null
  const currentId = Number(route.params.id)
  if (!currentId) return null
  return getNextProduct(currentId, productNavList.value)
})

const form = ref({
  name: '',
  name_ar: '',
  description: '',
  description_ar: '',
  price: 0,
  image: '',
  category_id: '',
  group_id: '',
  features: [] as string[],
  in_stock: true,
  is_visible: true,
  whatsapp_message: ''
})

const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const addFeature = () => {
  form.value.features.push('')
}

const removeFeature = (index: number) => {
  form.value.features.splice(index, 1)
}

const applyProductToForm = (product: any) => {
  form.value = {
    name: product.name,
    name_ar: product.name_ar,
    description: product.description,
    description_ar: product.description_ar || product.description,
    price: product.price,
    image: product.image || '',
    category_id: product.category_id || product.categoryId,
    group_id: product.group_id || '',
    features: product.features || [],
    in_stock: product.in_stock ?? true,
    is_visible: product.is_visible ?? true,
    whatsapp_message: product.whatsapp_message || '',
  }
  if (product.image) {
    imagePreview.value = mediaUrl(product.image)
  } else {
    imagePreview.value = null
  }
  imageFile.value = null
}

const goToNextProduct = async () => {
  const next = nextProduct.value
  if (!next || pageLoading.value || loading.value) return

  pageLoading.value = true
  success.value = ''
  error.value = ''

  try {
    await router.push({
      name: 'admin-product-edit',
      params: { id: String(next.id) },
    })
  } catch {
    pageLoading.value = false
  }
}

const fetchData = async () => {
  pageLoading.value = true
  try {
    const [categoriesRes, groupsRes] = await Promise.all([
      api.get('/categories'),
      api.get('/groups'),
    ])
    categories.value = categoriesRes.data
    groups.value = Array.isArray(groupsRes.data) ? groupsRes.data : []

    if (!productNavList.value.length) {
      const productsRes = await api.get('/products')
      const list = productsRes.data.products || productsRes.data
      productNavList.value = (Array.isArray(list) ? list : []).map((p: any) => ({
        id: p.id,
        name: p.name,
        name_ar: p.name_ar,
      }))
      saveProductNavList(productNavList.value)
    }

    if (!isNew.value) {
      const productRes = await api.get(`/products/${route.params.id}`)
      applyProductToForm(productRes.data)
    }
  } catch (err: any) {
    if (!isNew.value) {
      error.value = err.response?.data?.error || 'Failed to load data'
    }
  } finally {
    pageLoading.value = false
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('name_ar', form.value.name_ar)
    formData.append('description', form.value.description)
    formData.append('description_ar', form.value.description_ar)
    formData.append('price', form.value.price.toString())
    formData.append('category_id', form.value.category_id)
    if (form.value.group_id) {
      formData.append('group_id', String(form.value.group_id))
    }
    formData.append('in_stock', form.value.in_stock ? '1' : '0')
    formData.append('is_visible', form.value.is_visible ? '1' : '0')
    formData.append('whatsapp_message', form.value.whatsapp_message)
    
    // Add features as JSON
    formData.append('features', JSON.stringify(form.value.features))
    
    // Add image if uploaded
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    if (isNew.value) {
      const res = await api.post('/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      const productId = res.data?.id ?? res.data?.product?.id
      if (!productId) {
        throw new Error('Missing product id from server')
      }
      await router.replace({
        name: 'admin-product-edit',
        params: { id: String(productId) },
      })
      applyProductToForm(res.data?.product ?? res.data)
      success.value = 'تم إنشاء المنتج بنجاح'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    } else {
      const res = await api.post(`/products/${route.params.id}?_method=PUT`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      applyProductToForm(res.data?.product ?? res.data)
      success.value = 'تم تحديث المنتج بنجاح'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || err.message || 'Failed to save product'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

watch(
  () => route.params.id,
  (id, prev) => {
    if (id && id !== prev) {
      productNavList.value = readProductNavList()
      success.value = ''
      error.value = ''
      window.scrollTo({ top: 0, behavior: 'smooth' })
      fetchData()
    }
  },
)

onBeforeRouteUpdate(async (to, from) => {
  if (to.name === 'admin-product-edit' && from.name === 'admin-product-new') {
    await fetchData()
  }
})
</script>
