<template>
  <div class="min-h-screen bg-gray-50">
    <Header />
    
    <div class="pt-24 pb-16">
      <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-12">
          <router-link 
            to="/" 
            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 mb-6"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة للرئيسية | Back to Home
          </router-link>

          <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-blue-900 mb-1">
              منتجاتنا الكاملة | All Products
            </h1>
            <p class="text-xl text-gray-600">
              تصفح جميع الحلول الذكية المتوفرة لديك
            </p>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="mb-8 bg-white p-6 rounded-2xl shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                بحث | Search
              </label>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="ابحث عن منتج..."
                class="sf-field"
              />
            </div>

            <!-- Category Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                الفئة | Category
              </label>
              <select
                v-model="selectedCategory"
                class="sf-field"
              >
                <option value="">جميع الفئات | All Categories</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Stock Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                الحالة | Status
              </label>
              <select
                v-model="selectedStock"
                class="sf-field"
              >
                <option value="">جميع الحالات | All Status</option>
                <option value="in-stock">متوفر | In Stock</option>
                <option value="out-stock">غير متوفر | Out of Stock</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 6" :key="i" class="animate-pulse bg-white rounded-2xl p-6 h-96"></div>
        </div>

        <div v-else-if="filteredProducts.length > 0" class="sf-catalog-grid">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="bg-white rounded-[0.25rem] border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
          >
            <!-- Image Section -->
            <div class="sf-media-square">
              <img
                :src="mediaUrl(product.image)"
                :alt="product.name"
                @error="handleMediaError"
              />
              <!-- Stock Badge -->
              <div
                :class="[
                  'absolute top-4 right-4 px-3 py-1 rounded-md text-sm font-medium text-white',
                  product.in_stock ? 'bg-blue-600' : 'bg-red-600'
                ]"
              >
                {{ product.in_stock ? 'متوفر | In Stock' : 'غير متوفر | Out of Stock' }}
              </div>
            </div>

            <!-- Content Section -->
            <div class="p-4 border-t border-gray-200">
              <!-- Product Title -->
              <h3 class="text-lg font-bold text-gray-900 mb-1">
                {{ product.name }}
              </h3>
              <h4 class="text-sm text-gray-600 mb-1 font-arabic">
                {{ product.name_ar }}
              </h4>

              <!-- Category Badge -->
              <div v-if="product.category_id && categoryMap[product.category_id]" class="mb-1">
                <span class="inline-flex items-center gap-2 bg-white border border-blue-600 text-blue-600 text-sm px-3 py-1 rounded">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                  </svg>
                  {{ categoryMap[product.category_id].name }}
                </span>
              </div>

              <!-- Price -->
              <div class="text-2xl font-bold mb-1" style="color: #203c85;">
                {{ formatPrice(product.price) }} AED
              </div>

              <!-- Description -->
              <p v-if="product.description" class="text-gray-600 text-sm mb-1 line-clamp-2">
                {{ product.description }}
              </p>

              <!-- Features -->
              <div v-if="product.features && product.features.length > 0" class="mb-1 space-y-1">
                <div
                  v-for="(feature, idx) in product.features.slice(0, 2)"
                  :key="idx"
                  class="flex items-start gap-2 text-gray-700"
                >
                  <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: #203c85;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-xs">{{ feature }}</span>
                </div>
              </div>

              <!-- Details Button -->
              <router-link
                :to="{ name: 'product-details', params: { id: product.id } }"
                class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors mb-1 text-sm"
              >
                التفاصيل | Details
              </router-link>

              <!-- WhatsApp Button -->
              <a
                :href="getWhatsAppLink(product)"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full text-white text-center px-4 py-2 rounded-lg font-medium transition-colors items-center justify-center gap-2"
                style="background-color: #203c85;"
                @mouseover="$event.target.style.backgroundColor='#152a5c'"
                @mouseout="$event.target.style.backgroundColor='#203c85'"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                اطلب عبر واتساب
              </a>
            </div>
          </div>
        </div>

        <!-- No Products -->
        <div v-else class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <h3 class="text-2xl font-bold text-gray-900 mb-1">لا توجد منتجات</h3>
          <p class="text-gray-600">حاول تعديل معايير البحث أو الفلاتر</p>
        </div>

        <!-- Results Count -->
        <div v-if="filteredProducts.length > 0" class="text-center mt-8 text-gray-600">
          <p>عرض {{ filteredProducts.length }} من {{ allProducts.length }} منتجات</p>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface Product {
  id: number
  name: string
  name_ar: string
  brand: string
  price: string | number
  image: string
  in_stock: boolean
  category_id?: number
  features: string[]
  whatsapp_message: string
  description?: string
  description_ar?: string
}

interface Category {
  id: number
  name: string
  name_ar: string
}

interface CompanyInfo {
  contact: {
    whatsapp: string
  }
}

const formatPrice = (price: string | number): string => {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return numPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const allProducts = ref<Product[]>([])
const categories = ref<Category[]>([])
const companyInfo = ref<CompanyInfo | null>(null)
const loading = ref(true)

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStock = ref('')

const filteredProducts = computed(() => {
  let filtered = allProducts.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(p => 
      p.name.toLowerCase().includes(query) || 
      p.name_ar.toLowerCase().includes(query)
    )
  }

  // Category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(p => p.category_id === Number(selectedCategory.value))
  }

  // Stock filter
  if (selectedStock.value === 'in-stock') {
    filtered = filtered.filter(p => p.in_stock)
  } else if (selectedStock.value === 'out-stock') {
    filtered = filtered.filter(p => !p.in_stock)
  }

  return filtered
})

const categoryMap = computed(() => {
  return categories.value.reduce((map, cat) => {
    map[cat.id] = cat
    return map
  }, {} as Record<number, Category>)
})

const whatsappNumber = computed(() => {
  return companyInfo.value?.contact.whatsapp || '971562566232'
})

const getWhatsAppLink = (product: Product) => {
  const message = product.whatsapp_message || 
    `مرحباً، أرغب في الاستفسار عن المنتج: ${product.name_ar} (${product.name}) - السعر: ${formatPrice(product.price)} AED`
  return `https://wa.me/${whatsappNumber.value}?text=${encodeURIComponent(message)}`
}

onMounted(async () => {
  try {
    const [categoriesRes, productsRes, companyRes] = await Promise.all([
      fetch('/api/categories'),
      fetch('/api/products'),
      fetch('/company-info.json')
    ])

    const categoriesData = await categoriesRes.json()
    categories.value = Array.isArray(categoriesData) ? categoriesData : []

    const productsData = await productsRes.json()
    allProducts.value = productsData.products || []

    companyInfo.value = await companyRes.json()

    loading.value = false
  } catch (error) {
    console.error('Error loading data:', error)
    loading.value = false
  }
})
</script>

