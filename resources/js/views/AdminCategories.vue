<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">إدارة الفئات | Categories Management</h1>
    <div>
      <div class="sf-page-header">
        <h2 class="text-xl font-semibold">All Categories | جميع الفئات</h2>
        <RouterLink
          to="/admin/categories/new"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
        >
          + Add New Category | إضافة فئة جديدة
        </RouterLink>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        {{ error }}
      </div>

      <div v-else class="sf-card">
        <div class="sf-table-wrap">
        <table class="sf-table divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name (English)</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم (عربي)</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description | الوصف</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products | المنتجات</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions | الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="category in paginatedCategories" :key="category.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ category.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ category.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ category.name_ar }}</td>
              <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ category.description || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ category.products_count || 0 }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <div class="flex items-center justify-center gap-2">
                  <RouterLink
                    :to="`/admin/categories/${category.id}`"
                    class="text-blue-500 hover:text-blue-700 transition-colors"
                    title="تعديل"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </RouterLink>
                  <button
                    type="button"
                    @click="confirmDelete(category)"
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
                <span class="font-medium">{{ Math.min(endIndex, categories.length) }}</span>
                of
                <span class="font-medium">{{ categories.length }}</span>
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
          <p class="text-lg font-bold text-gray-900 mb-2">حذف الفئة؟</p>
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
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/api'

interface Category {
  id: number
  name: string
  name_ar: string
  name_ar: string
  description?: string
  products_count?: number
}

const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const deleteTarget = ref<Category | null>(null)
const deleteLoading = ref(false)

const totalPages = computed(() => Math.ceil(categories.value.length / itemsPerPage.value))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value)
const endIndex = computed(() => startIndex.value + itemsPerPage.value)
const paginatedCategories = computed(() => 
  categories.value.slice(startIndex.value, endIndex.value)
)

const fetchCategories = async () => {
  try {
    loading.value = true
    const response = await api.get('/categories')
    categories.value = response.data.map((cat: any) => ({
      ...cat,
      name_ar: cat.name_ar || cat.name_ar
    }))
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to load categories'
  } finally {
    loading.value = false
  }
}

const confirmDelete = (category: Category) => {
  deleteTarget.value = category
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await api.delete(`/categories/${deleteTarget.value.id}`)
    categories.value = categories.value.filter(c => c.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (err: any) {
    alert(err.response?.data?.error || 'Failed to delete category')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(() => {
  fetchCategories()
})
</script>
