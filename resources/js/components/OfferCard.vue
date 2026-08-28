<template>
  <article class="offer-card">
    <div class="offer-card__media">
      <img
        :src="mediaUrl(offer.image)"
        :alt="title"
        @error="handleMediaError"
      />
      <div class="offer-card__overlay" aria-hidden="true" />

      <div class="offer-card__price-float">
        <span class="offer-card__price-value">{{ formatPrice(offer.discounted_price) }}</span>
        <span class="offer-card__price-currency">AED</span>
        <span v-if="offer.original_price" class="offer-card__price-old">
          {{ formatPrice(offer.original_price) }}
        </span>
      </div>
    </div>

    <div class="offer-card__body">
      <p v-if="categoryName" class="offer-card__category">{{ categoryName }}</p>

      <h3 class="offer-card__title">{{ title }}</h3>

      <p v-if="description" class="offer-card__desc">{{ description }}</p>

      <ul v-if="visibleFeatures.length" class="offer-card__features">
        <li v-for="(feature, idx) in visibleFeatures" :key="idx">
          <span class="offer-card__feature-icon" aria-hidden="true">✓</span>
          {{ feature }}
        </li>
      </ul>

      <div class="offer-card__actions">
        <router-link
          :to="{ name: 'product-details', params: { id: offer.product_id || offer.id } }"
          class="btn-primary"
        >
          {{ t('viewDetails') }}
        </router-link>
        <a
          :href="whatsappLink"
          target="_blank"
          rel="noopener noreferrer"
          class="btn-whatsapp"
        >
          <svg class="wa-icon" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
            <path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/>
          </svg>
          {{ t('orderWhatsapp') }}
        </a>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useLocale } from '@/composables/useLocale'
import { mediaUrl, handleMediaError } from '@/lib/media'

export interface OfferItem {
  id: number
  product_id?: number
  name: string
  name_ar: string
  image: string
  original_price?: string | number
  discounted_price: string | number
  discount_percentage?: number
  in_stock: boolean
  category_id?: number
  features?: string[]
  description?: string
  description_ar?: string
  offer_description?: string
  offer_description_ar?: string
  whatsapp_message?: string
}

const props = defineProps<{
  offer: OfferItem
  categoryName?: string
  whatsappNumber: string
}>()

const { t, isAr, localized } = useLocale()

const formatPrice = (price: string | number): string => {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return numPrice.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

const title = computed(() => localized(props.offer))

const description = computed(() => {
  if (isAr.value) {
    return props.offer.offer_description_ar || props.offer.description_ar || props.offer.description || ''
  }
  return props.offer.offer_description || props.offer.description || props.offer.description_ar || ''
})

const visibleFeatures = computed(() => (props.offer.features || []).slice(0, 3))

const whatsappLink = computed(() => {
  const message =
    props.offer.whatsapp_message ||
    `مرحباً، أرغب في الاستفسار عن العرض: ${props.offer.name_ar} (${props.offer.name}) - السعر: ${formatPrice(props.offer.discounted_price)} AED`
  return `https://wa.me/${props.whatsappNumber}?text=${encodeURIComponent(message)}`
})
</script>
