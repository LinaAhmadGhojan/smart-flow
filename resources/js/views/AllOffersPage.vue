<template>
  <div class="min-h-screen sf-section">
    <Header />

    <div class="pt-28 pb-16">
      <div class="container mx-auto px-4">
        <div class="mb-12 max-w-3xl mx-auto">
          <router-link to="/" class="inline-flex items-center gap-2 text-[var(--sf-accent)] hover:text-[var(--sf-navy)] mb-6 text-sm font-semibold transition-colors">
            <span aria-hidden="true">{{ isAr ? '→' : '←' }}</span>
            {{ isAr ? 'العودة للرئيسية' : 'Back to Home' }}
          </router-link>

          <div class="text-center">
            <p class="sf-eyebrow">{{ t('limitedOffer') }}</p>
            <h1 class="sf-heading">{{ t('specialOffers') }}</h1>
            <p class="sf-subheading mt-3">{{ t('offersSubtitle') }}</p>
          </div>
        </div>

        <div v-if="loading" class="sf-catalog-grid">
          <div v-for="i in 6" :key="i" class="offer-card-skeleton">
            <div class="offer-card-skeleton__media" />
            <div class="offer-card-skeleton__body">
              <div class="offer-card-skeleton__line w-1/4" />
              <div class="offer-card-skeleton__line w-3/4" />
              <div class="offer-card-skeleton__line w-full" />
              <div class="offer-card-skeleton__line w-2/3" />
            </div>
          </div>
        </div>

        <div v-else-if="offers.length" class="sf-catalog-grid">
          <OfferCard
            v-for="offer in offers"
            :key="offer.id"
            :offer="offer"
            :whatsapp-number="whatsappNumber"
          />
        </div>

        <div v-else class="text-center py-16 rounded-2xl bg-white/70 border border-slate-200/80">
          <p class="text-xl font-semibold text-[var(--sf-navy)] mb-2">{{ t('noOffers') }}</p>
          <router-link to="/" class="btn-primary mt-4">
            {{ isAr ? 'العودة للرئيسية' : 'Back to Home' }}
          </router-link>
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
import OfferCard, { type OfferItem } from '@/components/OfferCard.vue'
import { useLocale } from '@/composables/useLocale'

interface CompanyInfo {
  contact: {
    whatsapp: string
  }
}

const { t, isAr } = useLocale()

const offers = ref<OfferItem[]>([])
const companyInfo = ref<CompanyInfo | null>(null)
const loading = ref(true)

const whatsappNumber = computed(() => companyInfo.value?.contact.whatsapp || '971562566232')

onMounted(async () => {
  try {
    const [offersRes, companyRes] = await Promise.all([
      fetch('/api/offers'),
      fetch('/company-info.json'),
    ])

    const offersData = await offersRes.json()
    const allOffers = offersData.offers || []
    offers.value = allOffers.filter((offer: OfferItem & { is_active?: boolean }) => offer.is_active)

    companyInfo.value = await companyRes.json()
  } catch (error) {
    console.error('Error loading offers:', error)
  } finally {
    loading.value = false
  }
})
</script>
