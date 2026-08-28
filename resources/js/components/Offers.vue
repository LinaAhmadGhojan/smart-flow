<template>
  <section id="offers" class="py-20 sf-section">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12 max-w-2xl mx-auto">
        <p class="sf-eyebrow">{{ t('limitedOffer') }}</p>
        <h2 class="sf-heading">{{ t('specialOffers') }}</h2>
        <p class="sf-subheading mt-3">{{ t('offersSubtitle') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
        <div v-for="i in 3" :key="i" class="offer-card-skeleton">
          <div class="offer-card-skeleton__media" />
          <div class="offer-card-skeleton__body">
            <div class="offer-card-skeleton__line w-1/4" />
            <div class="offer-card-skeleton__line w-3/4" />
            <div class="offer-card-skeleton__line w-full" />
            <div class="offer-card-skeleton__line w-2/3" />
          </div>
        </div>
      </div>

      <div v-else-if="offers.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
        <OfferCard
          v-for="offer in offers"
          :key="offer.id"
          :offer="offer"
          :category-name="getCategoryName(offer.category_id)"
          :whatsapp-number="whatsappNumber"
        />
      </div>

      <div v-else class="text-center py-14 rounded-2xl bg-white/70 border border-slate-200/80">
        <p class="text-lg text-slate-500">{{ t('noOffers') }}</p>
      </div>

      <div v-if="offers.length && !loading" class="text-center mt-10">
        <router-link to="/offers" class="btn-outline">
          {{ t('viewAllOffers') }}
          <span aria-hidden="true">{{ isAr ? '←' : '→' }}</span>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import OfferCard, { type OfferItem } from '@/components/OfferCard.vue'
import { useLocale } from '@/composables/useLocale'

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

const offers = ref<OfferItem[]>([])
const categories = ref<Category[]>([])
const companyInfo = ref<CompanyInfo | null>(null)
const loading = ref(true)

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

const getCategoryName = (categoryId?: number) => {
  if (!categoryId || !categoryMap.value[categoryId]) return ''
  return localized(categoryMap.value[categoryId])
}

onMounted(async () => {
  try {
    const [categoriesRes, offersRes, companyRes] = await Promise.all([
      fetch('/api/categories'),
      fetch('/api/offers'),
      fetch('/company-info.json'),
    ])

    const categoriesData = await categoriesRes.json()
    categories.value = Array.isArray(categoriesData) ? categoriesData : []

    const offersData = await offersRes.json()
    const allOffers = offersData.offers || offersData || []
    offers.value = allOffers.filter((offer: OfferItem & { is_active?: boolean }) => offer.is_active)

    companyInfo.value = await companyRes.json()
  } catch (error) {
    console.error('Error loading offers:', error)
  } finally {
    loading.value = false
  }
})
</script>
