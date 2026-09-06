<template>
  <div class="min-h-screen bg-gray-50">
    <Header />

    <main class="pt-28 pb-16">
      <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
          <div class="min-w-0 flex-1">
            <router-link to="/#products" class="text-sm text-blue-600 hover:underline">
              {{ isAr ? '←' : '→' }} {{ t('backToGroups') }}
            </router-link>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <div class="relative h-48 sm:h-56 bg-slate-100">
                <img
                  v-if="group?.image"
                  :src="mediaUrl(group.image)"
                  :alt="group ? localized(group) : ''"
                  class="w-full h-full object-cover"
                  @error="handleMediaError"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                  {{ t('groups') }}
                </div>
              </div>
              <div class="p-5 sm:p-6">
                <h1 class="text-start text-2xl sm:text-3xl font-bold text-blue-900">
                  {{ group ? localized(group) : t('productsInGroup') }}
                </h1>
                <p v-if="group" class="text-slate-500 mt-2 max-w-2xl">
                  {{
                    isAr
                      ? group.description_ar || group.description || ''
                      : group.description || group.description_ar || ''
                  }}
                </p>
              </div>
            </div>
          </div>
          <router-link
            to="/products"
            class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 text-sm font-medium"
          >
            {{ t('allProducts') }}
          </router-link>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="h-72 rounded-2xl bg-slate-100 animate-pulse" />
        </div>

        <div v-else-if="!products.length" class="text-center py-20 text-slate-500">
          {{ t('noProducts') }}
        </div>

        <div v-else class="sf-catalog-grid items-stretch">
          <article
            v-for="product in products"
            :key="product.id"
            class="product-card rounded-[0.25rem] flex flex-col h-full overflow-hidden"
          >
            <div class="sf-media-square shrink-0">
              <img
                :src="mediaUrl(product.image)"
                :alt="localized(product)"
                @error="handleMediaError"
              />
            </div>

            <div class="p-4 border-t border-gray-200 flex flex-col flex-1 min-h-0">
              <h3 class="text-lg font-semibold text-blue-900 mb-1 line-clamp-2">
                {{ localized(product) }}
              </h3>

              <div class="mb-3 flex-1 min-h-[4.5rem]">
                <p v-if="cardDescription(product)" class="text-sm text-slate-500 line-clamp-3">
                  {{ cardDescription(product) }}
                </p>
                <router-link
                  v-if="cardDescription(product)"
                  :to="`/products/${product.id}`"
                  class="inline-block mt-1 text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                  {{ t('readDetails') }}
                </router-link>
              </div>

              <div class="mt-auto space-y-2">
                <div class="flex gap-2">
                  <router-link
                    :to="`/products/${product.id}`"
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
                    {{ t('productCatalog') }}
                  </a>
                </div>
                <a
                  :href="getWhatsAppLink(product)"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex w-full items-center justify-center gap-2 text-white px-4 py-2 rounded-lg font-medium text-sm"
                  style="background-color: #203c85"
                >
                  {{ t('orderWhatsapp') }}
                </a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { mediaUrl, handleMediaError } from '@/lib/media'
import { useLocale } from '@/composables/useLocale'

interface Product {
  id: number
  name: string
  name_ar: string
  price: string | number
  image: string
  in_stock: boolean
  whatsapp_message?: string
  description?: string
  description_ar?: string
  data_sheet?: string | null
}

interface Group {
  id: number
  name: string
  name_ar: string
  image?: string | null
  description?: string | null
  description_ar?: string | null
  products?: Product[]
}

const route = useRoute()
const { t, isAr, localized } = useLocale()
const group = ref<Group | null>(null)
const loading = ref(true)
const companyWhatsapp = ref('971562566232')

const products = computed(() => group.value?.products || [])

const cardDescription = (product: Product) =>
  (
    isAr.value
      ? product.description_ar || product.description
      : product.description || product.description_ar || ''
  ).trim()

const getWhatsAppLink = (product: Product) => {
  const message =
    product.whatsapp_message ||
    `${t('whatsappInquirePrefix')} ${localized(product)}`
  return `https://wa.me/${companyWhatsapp.value}?text=${encodeURIComponent(message)}`
}

const loadGroup = async () => {
  loading.value = true
  try {
    const [groupRes, companyRes] = await Promise.all([
      fetch(`/api/groups/${route.params.id}`),
      fetch('/company-info.json'),
    ])
    if (!groupRes.ok) {
      group.value = null
      return
    }
    group.value = await groupRes.json()
    const company = await companyRes.json()
    companyWhatsapp.value = company?.contact?.whatsapp || companyWhatsapp.value
  } catch (error) {
    console.error(error)
    group.value = null
  } finally {
    loading.value = false
  }
}

onMounted(loadGroup)
watch(() => route.params.id, loadGroup)
</script>
