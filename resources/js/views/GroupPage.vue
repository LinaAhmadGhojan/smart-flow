<template>
  <div class="min-h-screen bg-[var(--sf-bg)]">
    <Header />

    <main class="pt-28 pb-16">
      <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
          <div class="min-w-0 flex-1">
            <router-link to="/#products" class="text-sm text-[var(--sf-accent)] hover:underline">
              ← {{ t('backToGroups') }}
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
                <h1 class="sf-heading text-start text-2xl sm:text-3xl">
                  {{ group ? localized(group) : t('productsInGroup') }}
                </h1>
                <p v-if="group" class="text-slate-500 mt-2 max-w-2xl">
                  {{
                    isAr
                      ? (group.description_ar || group.description || '')
                      : (group.description || group.description_ar || '')
                  }}
                </p>
              </div>
            </div>
          </div>
          <router-link to="/products" class="btn-outline">{{ t('allProducts') }}</router-link>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="h-72 rounded-2xl bg-slate-100 animate-pulse" />
        </div>

        <div v-else-if="!products.length" class="text-center py-20 text-slate-500">
          {{ t('noProducts') }}
        </div>

        <div v-else class="sf-catalog-grid">
          <article
            v-for="product in products"
            :key="product.id"
            class="product-card rounded-[0.25rem]"
          >
            <router-link :to="`/products/${product.id}`" class="block">
              <div class="sf-media-square">
                <img
                  :src="mediaUrl(product.image)"
                  :alt="localized(product)"
                  class="transition-transform duration-500 hover:scale-105"
                  @error="handleMediaError"
                />
                
              </div>
            </router-link>

            <div class="p-5">
              <h3 class="text-lg font-semibold text-[var(--sf-navy)] mb-1">
                {{ localized(product) }}
              </h3>
              <p class="text-sm text-slate-500 mb-3 line-clamp-1">
                {{ isAr ? product.name : product.name_ar }}
              </p>
              <div class="text-2xl font-bold text-[var(--sf-navy)] mb-4">
                {{ formatPrice(product.price) }} AED
              </div>
              <a
                :href="getWhatsAppLink(product)"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-primary w-full justify-center"
              >
                {{ t('orderWhatsapp') }}
              </a>
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
import { useLocale } from '@/composables/useLocale'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface Product {
  id: number
  name: string
  name_ar: string
  price: string | number
  image: string
  in_stock: boolean
  whatsapp_message?: string
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

const formatPrice = (price: string | number) => {
  const num = typeof price === 'string' ? parseFloat(price) : price
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const getWhatsAppLink = (product: Product) => {
  const message = product.whatsapp_message ||
    (isAr.value
      ? `مرحباً، أرغب في الاستفسار عن المنتج: ${product.name_ar}`
      : `Hello, I want to inquire about: ${product.name}`)
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
