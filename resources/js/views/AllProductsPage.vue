<template>
  <div class="min-h-screen bg-gray-50">
    <Header />

    <div class="pt-24 pb-16">
      <div class="container mx-auto px-4">
        <div class="mb-12">
          <router-link
            to="/"
            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 mb-6"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ t('backHome') }}
          </router-link>

          <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-blue-900 mb-1">{{ t('fullCatalog') }}</h1>
            <p class="text-xl text-gray-600">{{ t('browseCatalog') }}</p>
          </div>
        </div>

        <div class="mb-8 bg-white p-6 rounded-2xl shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('search') }}</label>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="t('searchProduct')"
                class="sf-field"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('category') }}</label>
              <select v-model="selectedCategory" class="sf-field">
                <option value="">{{ t('allCategories') }}</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ localized(cat) }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('status') }}</label>
              <select v-model="selectedStock" class="sf-field">
                <option value="">{{ t('allStatuses') }}</option>
                <option value="in-stock">{{ t('inStock') }}</option>
                <option value="out-stock">{{ t('outOfStock') }}</option>
              </select>
            </div>
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 6" :key="i" class="animate-pulse bg-white rounded-2xl p-6 h-96" />
        </div>

        <div v-else-if="filteredProducts.length > 0" class="sf-catalog-grid items-stretch">
          <article
            v-for="product in filteredProducts"
            :key="product.id"
            class="product-card rounded-[0.25rem] flex flex-col h-full overflow-hidden"
          >
            <div class="sf-media-square shrink-0 relative">
              <img
                :src="mediaUrl(product.image)"
                :alt="displayName(product)"
                @error="handleMediaError"
              />
              <div
                :class="[
                  'absolute top-4 px-3 py-1 rounded-md text-sm font-medium text-white',
                  isAr ? 'right-4' : 'left-4',
                  product.in_stock ? 'bg-blue-600' : 'bg-red-600',
                ]"
              >
                {{ product.in_stock ? t('inStock') : t('outOfStock') }}
              </div>
            </div>

            <div class="p-4 border-t border-gray-200 flex flex-col flex-1 min-h-0">
              <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">
                {{ displayName(product) }}
              </h3>

              <div v-if="product.category_id && categoryMap[product.category_id]" class="mb-2">
                <span class="inline-flex items-center gap-2 bg-white border border-blue-600 text-blue-600 text-xs px-2.5 py-0.5 rounded">
                  {{ localized(categoryMap[product.category_id]) }}
                </span>
              </div>

              <div class="mb-3 flex-1 min-h-[4.5rem]">
                <p v-if="cardDescription(product)" class="text-gray-600 text-sm line-clamp-3">
                  {{ cardDescription(product) }}
                </p>
                <router-link
                  v-if="cardDescription(product)"
                  :to="{ name: 'product-details', params: { id: product.id } }"
                  class="inline-block mt-1 text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                  {{ t('readDetails') }}
                </router-link>
              </div>

              <div class="mt-auto space-y-2">
                <div class="flex gap-2">
                  <router-link
                    :to="{ name: 'product-details', params: { id: product.id } }"
                    :class="[
                      'bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded-lg font-medium transition-colors text-sm',
                      product.data_sheet ? 'w-1/2' : 'w-full',
                    ]"
                  >
                    {{ t('viewDetails') }}
                  </router-link>
                  <a
                    v-if="product.data_sheet"
                    :href="mediaUrl(product.data_sheet, '#')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-1/2 inline-flex items-center justify-center gap-1.5 border border-[#203c85] text-[#203c85] hover:bg-[#e8eef9] text-center px-3 py-2 rounded-lg font-medium transition-colors text-sm"
                  >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    {{ t('productCatalog') }}
                  </a>
                </div>

                <a
                  :href="getWhatsAppLink(product)"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex w-full text-white text-center px-4 py-2 rounded-lg font-medium items-center justify-center gap-2 text-sm"
                  style="background-color: #203c85"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                  </svg>
                  {{ t('orderWhatsapp') }}
                </a>
              </div>
            </div>
          </article>
        </div>

        <div v-else class="text-center py-12">
          <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ t('noProductsFound') }}</h3>
          <p class="text-gray-600">{{ t('adjustFilters') }}</p>
        </div>

        <div v-if="filteredProducts.length > 0" class="text-center mt-8 text-gray-600">
          <p>
            {{ t('showingOf') }} {{ filteredProducts.length }} {{ t('of') }}
            {{ allProducts.length }} {{ t('productUnit') }}
          </p>
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
import { useLocale } from '@/composables/useLocale'

interface Product {
  id: number
  name: string
  name_ar: string
  brand?: string
  price: string | number
  image: string
  in_stock: boolean
  category_id?: number
  features?: string[]
  whatsapp_message?: string
  description?: string
  description_ar?: string
  data_sheet?: string | null
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

const { t, isAr, localized } = useLocale()

const formatPrice = (price: string | number): string => {
  const num = typeof price === 'string' ? parseFloat(price) : price
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const allProducts = ref<Product[]>([])
const categories = ref<Category[]>([])
const companyInfo = ref<CompanyInfo | null>(null)
const loading = ref(true)

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStock = ref('')

const displayName = (product: Product) => localized(product)

const cardDescription = (product: Product) =>
  (
    isAr.value
      ? product.description_ar || product.description
      : product.description || product.description_ar || ''
  ).trim()

const filteredProducts = computed(() => {
  let filtered = allProducts.value

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(
      (p) => p.name.toLowerCase().includes(q) || (p.name_ar || '').toLowerCase().includes(q),
    )
  }

  if (selectedCategory.value) {
    filtered = filtered.filter((p) => p.category_id === Number(selectedCategory.value))
  }

  if (selectedStock.value === 'in-stock') {
    filtered = filtered.filter((p) => p.in_stock)
  } else if (selectedStock.value === 'out-stock') {
    filtered = filtered.filter((p) => !p.in_stock)
  }

  return filtered
})

const categoryMap = computed(() =>
  categories.value.reduce(
    (map, cat) => {
      map[cat.id] = cat
      return map
    },
    {} as Record<number, Category>,
  ),
)

const whatsappNumber = computed(() => companyInfo.value?.contact.whatsapp || '971562566232')

const getWhatsAppLink = (product: Product) => {
  const message =
    product.whatsapp_message ||
    `${t('whatsappInquirePrefix')} ${displayName(product)} - ${t('price')}: ${formatPrice(product.price)} AED`
  return `https://wa.me/${whatsappNumber.value}?text=${encodeURIComponent(message)}`
}

onMounted(async () => {
  try {
    const [categoriesRes, productsRes, companyRes] = await Promise.all([
      fetch('/api/categories'),
      fetch('/api/products'),
      fetch('/company-info.json'),
    ])

    const categoriesData = await categoriesRes.json()
    categories.value = Array.isArray(categoriesData)
      ? categoriesData
      : categoriesData.categories || []

    const productsData = await productsRes.json()
    allProducts.value = productsData.products || (Array.isArray(productsData) ? productsData : [])

    companyInfo.value = await companyRes.json()
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
})
</script>
