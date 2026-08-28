<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">إدارة المنتجات | Products Management</h1>
    <div>
      <div class="sf-page-header">
        <h2 class="text-xl font-semibold">All Products | جميع المنتجات</h2>
        <RouterLink
          to="/admin/products/new"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
        >
          + Add New Product | إضافة منتج جديد
        </RouterLink>
      </div>

      <div class="mb-4 relative max-w-md">
        <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
        </svg>
        <input
          v-model="search"
          type="text"
          placeholder="Search English / Arabic name, brand, description..."
          class="w-full bg-white border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        {{ error }}
      </div>

      <div v-else class="sf-card">
        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-gray-100">
          <div
            v-for="product in paginatedProducts"
            :key="'mobile-' + product.id"
            class="p-4 flex items-start gap-3"
          >
            <div class="shrink-0">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="h-16 w-16 object-cover rounded-lg border border-gray-100"
              />
              <div v-else class="h-16 w-16 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">No Image</div>
            </div>

            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 leading-snug">{{ product.name_ar || product.name }}</p>
              <p v-if="product.name_ar && product.name" class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ product.name }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ getCategoryName(product.category_id || product.categoryId) }}</p>
              <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="text-sm font-bold text-blue-700">{{ product.price }} AED</span>
                <span
                  class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium"
                  :class="product.is_visible !== false ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ product.is_visible !== false ? 'ظاهر' : 'إدارة فقط' }}
                </span>
              </div>
            </div>

            <div class="flex flex-col gap-2 shrink-0 pt-1">
              <RouterLink
                :to="`/admin/products/${product.id}`"
                class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors"
                title="تعديل"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </RouterLink>
              <button
                type="button"
                @click="confirmDelete(product)"
                class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors"
                title="حذف"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block sf-table-wrap">
        <table class="sf-table divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visibility</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in paginatedProducts" :key="product.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <img v-if="product.image" :src="product.image" :alt="product.name" class="h-12 w-12 object-cover rounded" />
                <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center text-gray-400">No Image</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                <div class="text-sm text-gray-500">{{ product.name_ar }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ getCategoryName(product.category_id || product.categoryId) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ product.price }} AED
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span
                  class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="product.is_visible !== false ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ product.is_visible !== false ? 'ظاهر للعملاء' : 'للإدارة فقط' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center justify-center gap-2">
                  <RouterLink
                    :to="`/admin/products/${product.id}`"
                    class="text-blue-500 hover:text-blue-700 transition-colors"
                    title="تعديل"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </RouterLink>
                  <button
                    type="button"
                    @click="confirmDelete(product)"
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

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ startIndex + 1 }}</span>
                to
                <span class="font-medium">{{ Math.min(endIndex, filteredProducts.length) }}</span>
                of
                <span class="font-medium">{{ filteredProducts.length }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="currentPage--"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="currentPage = page"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    currentPage === page
                      ? 'z-10 bg-blue-600 border-blue-600 text-white'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="currentPage++"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف المنتج؟</p>
          <p class="text-gray-500 text-sm mb-6">{{ deleteTarget.name_ar || deleteTarget.name }}</p>
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
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/api'
import { filterProducts as filterProductList, type SearchableProduct } from '@/lib/productSearch'

interface Category {
  id: number
  name: string
  name_ar: string
}

interface Product extends SearchableProduct {
  description: string
  description_ar: string
  price: number
  image: string | null
  categoryId: number
  category_id?: number
  is_visible?: boolean
}

const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const deleteTarget = ref<Product | null>(null)
const deleteLoading = ref(false)

const categoryById = computed(() =>
  categories.value.reduce((map, cat) => {
    map[cat.id] = cat
    return map
  }, {} as Record<number, Category>)
)

const searchableProducts = computed(() =>
  products.value.map((p) => ({
    ...p,
    category: categoryById.value[p.category_id || p.categoryId] || null,
  }))
)

const filteredProducts = computed(() => {
  const q = search.value.trim()
  if (!q) return searchableProducts.value
  return filterProductList(searchableProducts.value, q, searchableProducts.value.length)
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value) || 1)
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value)
const endIndex = computed(() => startIndex.value + itemsPerPage.value)
const paginatedProducts = computed(() =>
  filteredProducts.value.slice(startIndex.value, endIndex.value)
)

const getCategoryName = (categoryId: number) => {
  const category = categories.value.find(c => c.id === categoryId)
  return category ? `${category.name} | ${category.name_ar}` : 'Unknown'
}

watch(search, () => {
  currentPage.value = 1
})

const fetchData = async () => {
  try {
    loading.value = true
    const [productsRes, categoriesRes] = await Promise.all([
      api.get('/products'),
      api.get('/categories')
    ])
    products.value = productsRes.data.products || productsRes.data
    categories.value = categoriesRes.data
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to load data'
  } finally {
    loading.value = false
  }
}

const confirmDelete = (product: Product) => {
  deleteTarget.value = product
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await api.delete(`/products/${deleteTarget.value.id}`)
    products.value = products.value.filter(p => p.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (err: any) {
    alert(err.response?.data?.error || 'Failed to delete product')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
