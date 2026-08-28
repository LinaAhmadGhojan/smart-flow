<template>
  <section id="products" class="py-20 sf-section">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12 max-w-2xl mx-auto">
        <p class="sf-eyebrow">{{ t('groups') }}</p>
        <h2 class="sf-heading">{{ t('ourGroups') }}</h2>
        <p class="sf-subheading mt-3">{{ t('groupsSubtitle') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="h-72 rounded-2xl bg-slate-100 animate-pulse" />
      </div>

      <div v-else-if="!groups.length" class="text-center py-12 text-slate-500">
        {{ isAr ? 'لا توجد مجموعات حالياً' : 'No groups yet' }}
      </div>

      <div v-else class="sf-catalog-grid">
        <router-link
          v-for="group in groups"
          :key="group.id"
          :to="`/groups/${group.id}`"
          class="group-image-card"
        >
          <div class="group-image-card__media">
            <img
              :src="mediaUrl(group.image)"
              :alt="localized(group)"
              class="w-full h-full object-cover object-center"
              @error="handleMediaError"
            />
            <div class="group-image-card__overlay" />
          </div>
          <div class="group-image-card__body">
            <h3 class="text-xl font-semibold text-[var(--sf-navy)] mb-1">
              {{ localized(group) }}
            </h3>
            <p class="text-sm text-slate-500 line-clamp-2 mb-4">
              {{
                isAr
                  ? (group.description_ar || group.description || t('viewGroup'))
                  : (group.description || group.description_ar || t('viewGroup'))
              }}
            </p>
            <div class="flex items-center justify-between gap-3 text-sm">
              <span class="text-slate-600">
                {{ group.products_count || 0 }}
                {{ isAr ? 'منتج' : 'products' }}
              </span>
              <span class="text-[var(--sf-accent)] font-medium inline-flex items-center gap-1">
                {{ t('viewGroup') }}
                <span aria-hidden="true">{{ isAr ? '←' : '→' }}</span>
              </span>
            </div>
          </div>
        </router-link>
      </div>

      <div class="text-center mt-10">
        <router-link to="/products" class="btn-outline">
          {{ t('allProducts') }}
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLocale } from '@/composables/useLocale'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface Group {
  id: number
  name: string
  name_ar: string
  image?: string | null
  description?: string | null
  description_ar?: string | null
  products_count?: number
}

const { t, isAr, localized } = useLocale()
const groups = ref<Group[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await fetch('/api/groups')
    const data = await res.json()
    groups.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error('Error loading groups:', error)
  } finally {
    loading.value = false
  }
})
</script>
