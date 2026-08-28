<template>
  <section id="home" class="hero-section">
    <div class="hero-section__bg" aria-hidden="true" />
    <div class="container mx-auto px-4 relative z-10">
      <div class="hero-grid pt-28 pb-16">
        <div :class="['hero-content', isAr ? 'md:order-2' : '']">
          <div class="hero-badge">
            <span class="hero-badge__dot" aria-hidden="true" />
            {{ t('heroBadge') }}
          </div>

          <h1 class="hero-title">
            <span class="hero-title__line">
              {{ t('heroTitleLine1') }}
              <span class="hero-title__accent">{{ t('heroTitleAccent') }}</span>
            </span>
            <span class="hero-title__line">{{ t('heroTitleLine2') }}</span>
          </h1>

          <p class="hero-subtitle">
            {{ t('heroSubtitle') }}
          </p>

          <ul class="hero-features">
            <li v-for="feature in features" :key="feature">
              <span class="hero-features__icon" aria-hidden="true">✓</span>
              {{ feature }}
            </li>
          </ul>

          <div class="hero-actions">
            <button type="button" class="btn-primary" @click="scrollToProducts">
              {{ t('viewProducts') }}
            </button>
            <a
              :href="`https://wa.me/${whatsapp}`"
              target="_blank"
              rel="noopener noreferrer"
              class="btn-whatsapp"
            >
              <svg class="wa-icon" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                <path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/>
              </svg>
              {{ t('contactUs') }}
            </a>
          </div>

          <div class="hero-stats">
            <div v-for="stat in stats" :key="stat.label" class="hero-stat">
              <div class="hero-stat__value">{{ stat.value }}</div>
              <div class="hero-stat__label">{{ stat.label }}</div>
            </div>
          </div>
        </div>

        <div :class="['hero-visual-wrap', isAr ? 'md:order-1' : '']">
          <div class="hero-offers" aria-roledescription="carousel" :aria-label="t('specialOffers')">
            <div class="hero-offers__header">
              <p class="hero-offers__eyebrow">{{ t('limitedOffer') }}</p>
              <router-link to="/offers" class="hero-offers__all">
                {{ t('viewAllOffers') }}
                <span aria-hidden="true">{{ isAr ? '←' : '→' }}</span>
              </router-link>
            </div>

            <div v-if="loading" class="hero-offer-slide hero-offer-slide--skeleton">
              <div class="hero-offer-slide__media skeleton-pulse" />
              <div class="hero-offer-slide__body">
                <div class="skeleton-line w-1/3" />
                <div class="skeleton-line w-3/4" />
                <div class="skeleton-line w-1/2" />
              </div>
            </div>

            <div v-else-if="!offers.length" class="hero-offers__empty">
              <p>{{ t('noOffers') }}</p>
            </div>

            <template v-else>
              <div class="hero-offers__viewport">
                <Transition :name="slideDir" mode="out-in">
                  <article
                    :key="currentOffer.id"
                    class="hero-offer-slide"
                    @mouseenter="pause"
                    @mouseleave="resume"
                  >
                    <div class="hero-offer-slide__media">
                      <img
                        :src="mediaUrl(currentOffer.image)"
                        :alt="offerTitle(currentOffer)"
                        @error="handleMediaError"
                      />
                      <div class="hero-offer-slide__price">
                        <span class="hero-offer-slide__price-value">
                          {{ formatPrice(currentOffer.discounted_price) }}
                        </span>
                        <span class="hero-offer-slide__price-currency">AED</span>
                        <span
                          v-if="currentOffer.original_price"
                          class="hero-offer-slide__price-old"
                        >
                          {{ formatPrice(currentOffer.original_price) }}
                        </span>
                      </div>
                      <span
                        v-if="currentOffer.discount_percentage"
                        class="hero-offer-slide__badge"
                      >
                        −{{ currentOffer.discount_percentage }}%
                      </span>
                    </div>

                    <div class="hero-offer-slide__body">
                      <h2 class="hero-offer-slide__title">{{ offerTitle(currentOffer) }}</h2>
                      <p v-if="offerDesc(currentOffer)" class="hero-offer-slide__desc">
                        {{ offerDesc(currentOffer) }}
                      </p>
                      <div class="hero-offer-slide__actions">
                        <router-link
                          :to="{ name: 'product-details', params: { id: currentOffer.product_id || currentOffer.id } }"
                          class="btn-primary"
                        >
                          {{ t('viewDetails') }}
                        </router-link>
                        <a
                          :href="whatsappLink(currentOffer)"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="btn-whatsapp"
                        >
                          {{ t('orderWhatsapp') }}
                        </a>
                      </div>
                    </div>
                  </article>
                </Transition>
              </div>

              <div v-if="offers.length > 1" class="hero-offers__footer">
                <div class="hero-offers__dots" role="tablist">
                  <button
                    v-for="(offer, index) in offers"
                    :key="offer.id"
                    type="button"
                    role="tab"
                    class="hero-offers__dot"
                    :class="{ 'is-active': index === currentIndex }"
                    :aria-label="`${index + 1} / ${offers.length}`"
                    :aria-selected="index === currentIndex"
                    @click="goTo(index)"
                  />
                </div>
                <div class="hero-offers__progress" aria-hidden="true">
                  <div
                    class="hero-offers__progress-bar"
                    :class="{ 'is-paused': paused }"
                    :key="progressKey"
                  />
                </div>
              </div>
              <div v-else class="pb-4" />
            </template>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useLocale } from '@/composables/useLocale'
import { handleMediaError, mediaUrl } from '@/lib/media'
import type { OfferItem } from '@/components/OfferCard.vue'

const { t, isAr, localized } = useLocale()
const whatsapp = '971562566232'
const INTERVAL_MS = 5000

const offers = ref<OfferItem[]>([])
const loading = ref(true)
const currentIndex = ref(0)
const paused = ref(false)
const slideDir = ref('hero-slide-next')
const progressKey = ref(0)

let timer: ReturnType<typeof setInterval> | null = null

const features = computed(() => [
  t('heroFeature1'),
  t('heroFeature2'),
  t('heroFeature3'),
])

const stats = computed(() => [
  { value: '1000+', label: t('projectsDone') },
  { value: '500+', label: t('happyClients') },
  { value: '15+', label: t('yearsExp') },
])

const currentOffer = computed(() => offers.value[currentIndex.value])

const formatPrice = (price: string | number): string => {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return numPrice.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

const offerTitle = (offer: OfferItem) => localized(offer)

const offerDesc = (offer: OfferItem) => {
  if (isAr.value) {
    return offer.offer_description_ar || offer.description_ar || offer.description || ''
  }
  return offer.offer_description || offer.description || offer.description_ar || ''
}

const whatsappLink = (offer: OfferItem) => {
  const message =
    offer.whatsapp_message ||
    `مرحباً، أرغب في الاستفسار عن العرض: ${offer.name_ar} (${offer.name}) - السعر: ${formatPrice(offer.discounted_price)} AED`
  return `https://wa.me/${whatsapp}?text=${encodeURIComponent(message)}`
}

const clearTimer = () => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

const startTimer = () => {
  clearTimer()
  if (offers.value.length < 2 || paused.value) return
  progressKey.value += 1
  timer = setInterval(() => {
    slideDir.value = 'hero-slide-next'
    currentIndex.value = (currentIndex.value + 1) % offers.value.length
    progressKey.value += 1
  }, INTERVAL_MS)
}

const pause = () => {
  paused.value = true
  clearTimer()
}

const resume = () => {
  paused.value = false
  startTimer()
}

const goTo = (index: number) => {
  if (index === currentIndex.value) return
  slideDir.value = index > currentIndex.value ? 'hero-slide-next' : 'hero-slide-prev'
  currentIndex.value = index
  progressKey.value += 1
  if (!paused.value) startTimer()
}

const scrollToProducts = () => {
  document.getElementById('products')?.scrollIntoView({ behavior: 'smooth' })
}

onMounted(async () => {
  try {
    const offersRes = await fetch('/api/offers')
    const offersData = await offersRes.json()
    const allOffers = offersData.offers || offersData || []
    offers.value = allOffers.filter((offer: OfferItem & { is_active?: boolean }) => offer.is_active)
  } catch (error) {
    console.error('Error loading hero offers:', error)
  } finally {
    loading.value = false
    startTimer()
  }
})

onUnmounted(() => {
  clearTimer()
})
</script>
