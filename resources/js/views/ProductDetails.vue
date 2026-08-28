<template>
  <div class="min-h-screen bg-gray-50">
    <Header />
    
    <div class="pt-24 pb-16">
      <div class="container mx-auto px-4">
        <!-- Back Button -->
        <router-link 
          to="/" 
          class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 mb-8"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          العودة للرئيسية | Back to Home
        </router-link>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600">جاري التحميل...</p>
          </div>
        </div>

        <!-- Product Details -->
        <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Left Column - Images -->
          <div>
            <!-- Main Image -->
            <div class="bg-white rounded-2xl p-4 sm:p-6 mb-6">
              <div class="sf-media-square rounded-[0.25rem]">
                <img
                  :src="mainImage"
                  :alt="product.name"
                />
              </div>
            </div>

            <!-- Stock Status -->
            <div class="bg-white rounded-2xl p-6">
              <div
                :class="[
                  'flex items-center gap-3 p-4 rounded-lg',
                  product.in_stock 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800'
                ]"
              >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="product.in_stock" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                  <p class="font-bold text-lg">
                    {{ product.in_stock ? 'متوفر الآن | In Stock' : 'غير متوفر | Out of Stock' }}
                  </p>
                  <p class="text-sm">
                    {{ product.in_stock 
                      ? 'المنتج متاح للطلب الفوري' 
                      : 'المنتج غير متاح حالياً, اترك بيانات للتنبيه' 
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Details -->
          <div>
            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-1">
              {{ product.name }}
            </h1>
            <h2 class="text-2xl text-gray-700 mb-6 font-arabic">
              {{ product.name_ar }}
            </h2>

            <!-- Category -->
            <div v-if="product.category_id && categoryMap[product.category_id]" class="mb-6">
              <span class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 text-sm px-4 py-2 rounded-full font-semibold">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                </svg>
                {{ categoryMap[product.category_id].name }}
              </span>
            </div>

            <!-- Price Section -->
            <div class="rounded-2xl p-8 mb-8" style="background: linear-gradient(to bottom right, #e8eef9, #d4dff5);">
              <p class="text-gray-600 text-sm mb-1">السعر | Price</p>
              <div class="text-5xl font-bold" style="color: #203c85;">
                {{ formatPrice(product.price) }} <span class="text-2xl">AED</span>
              </div>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="mb-8">
              <h3 class="text-2xl font-bold text-gray-900 mb-1">الوصف | Description</h3>
              <p class="text-gray-700 text-lg leading-relaxed">
                {{ product.description }}
              </p>
            </div>

            <!-- Features -->
            <div v-if="product.features && product.features.length > 0" class="mb-8">
              <h3 class="text-2xl font-bold text-gray-900 mb-1">المميزات | Features</h3>
              <div class="space-y-3">
                <div
                  v-for="(feature, idx) in product.features"
                  :key="idx"
                  class="flex items-start gap-4 p-4 bg-white rounded-lg border border-gray-200"
                >
                  <svg class="w-6 h-6 mt-0.5 flex-shrink-0" style="color: #203c85;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <p class="text-gray-700 text-lg">{{ feature }}</p>
                </div>
              </div>
            </div>

            <!-- Brand Info -->
            <div v-if="product.brand" class="mb-8 p-6 bg-white rounded-xl border border-gray-200">
              <p class="text-gray-600 text-sm mb-1">الماركة | Brand</p>
              <p class="text-2xl font-bold text-gray-900">{{ product.brand }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
              <!-- WhatsApp Contact Button -->
              <a
                :href="getWhatsAppLink(product)"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full text-white text-center px-8 py-4 rounded-xl font-bold text-lg transition-colors items-center justify-center gap-3"
                style="background-color: #203c85;"
                @mouseover="$event.target.style.backgroundColor='#152a5c'"
                @mouseout="$event.target.style.backgroundColor='#203c85'"
              >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                {{ product.in_stock ? 'اطلب الآن عبر واتساب' : 'استفسر عبر واتساب' }}
              </a>

              <!-- View All Products Button -->
              <router-link
                :to="{ name: 'all-products' }"
                class="flex w-full bg-blue-600 hover:bg-blue-700 text-white text-center px-8 py-4 rounded-xl font-bold text-lg transition-colors items-center justify-center gap-3"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                عرض جميع المنتجات
              </router-link>
            </div>
          </div>
        </div>

        <!-- Not Found -->
        <div v-else class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.343 17.657l1.414-1.414m2.828 0l1.415 1.415m2.828 0l1.414-1.414m2.829 0l1.415 1.415M9 11a3 3 0 106 0 3 3 0 00-6 0z"/>
          </svg>
          <h3 class="text-2xl font-bold text-gray-900 mb-1">المنتج غير موجود</h3>
          <p class="text-gray-600 mb-6">عذراً, المنتج الذي تبحث عنه غير متوفر</p>
          <router-link
            to="/"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
          >
            العودة للرئيسية
          </router-link>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

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

const route = useRoute()

const formatPrice = (price: string | number): string => {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return numPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const product = ref<Product | null>(null)
const categories = ref<Category[]>([])
const companyInfo = ref<CompanyInfo | null>(null)
const loading = ref(true)

const mainImage = computed(() => {
  return product.value?.image || '/placeholder-product.jpg'
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

const getWhatsAppLink = (prod: Product) => {
  const message = prod.whatsapp_message || 
    `مرحباً، أرغب في الاستفسار عن المنتج: ${prod.name_ar} (${prod.name}) - السعر: ${formatPrice(prod.price)} AED`
  return `https://wa.me/${whatsappNumber.value}?text=${encodeURIComponent(message)}`
}

onMounted(async () => {
  try {
    const productId = route.params.id

    const [categoriesRes, productRes, companyRes] = await Promise.all([
      fetch('/api/categories'),
      fetch(`/api/products/${productId}`),
      fetch('/company-info.json')
    ])

    const categoriesData = await categoriesRes.json()
    categories.value = Array.isArray(categoriesData) ? categoriesData : []

    const productData = await productRes.json()
    product.value = productData.data || productData || null

    companyInfo.value = await companyRes.json()

    // Scroll to top
    window.scrollTo(0, 0)

    loading.value = false
  } catch (error) {
    console.error('Error loading product:', error)
    loading.value = false
  }
})
</script>

