<template>
  <header class="site-header fixed top-0 inset-x-0 z-50">
    <div class="container mx-auto px-4 py-3">
      <div class="flex items-center justify-between gap-4">
        <router-link to="/" class="flex items-center gap-3 min-w-0">
          <img src="/logo.jpeg" alt="SmartFlow" class="h-11 w-11 rounded-lg object-contain bg-white shadow-sm" />
          <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-white truncate">
              {{ companyInfo?.companyName || 'SMARTFLOW' }}
            </h1>
            <p class="text-[11px] text-slate-300 truncate">
              {{ isAr ? (companyInfo?.tagline_ar || 'منزلك آمن معنا') : (companyInfo?.tagline || 'Your Home is Safe With Us') }}
            </p>
          </div>
        </router-link>

        <nav class="hidden lg:flex items-center gap-1">
          <router-link to="/" class="nav-link">{{ t('home') }}</router-link>
          <router-link to="/offers" class="nav-link">{{ t('offers') }}</router-link>
          <router-link to="/products" class="nav-link">{{ t('allProducts') }}</router-link>
          <router-link to="/projects" class="nav-link">{{ t('ourProjects') }}</router-link>

          <button
            type="button"
            class="lang-toggle mx-2"
            :aria-label="t('language')"
            @click="toggleLocale"
          >
            <span class="lang-toggle__icon" aria-hidden="true">文A</span>
            <span>{{ locale === 'ar' ? 'EN' : 'ع' }}</span>
          </button>

          <router-link to="/project-study" class="btn-primary">
            {{ t('projectStudy') }}
          </router-link>
          <router-link to="/gate-machine-study" class="btn-header-secondary">
            {{ t('gateMachineStudy') }}
          </router-link>
          <a
            :href="`https://wa.me/${whatsappNumber}`"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-whatsapp"
          >
            <svg class="wa-icon" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
              <path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/>
            </svg>
            {{ t('whatsapp') }}
          </a>
        </nav>

        <div class="flex items-center gap-2 lg:hidden">
          <button type="button" class="lang-toggle" @click="toggleLocale">
            <span class="lang-toggle__icon" aria-hidden="true">文A</span>
            <span>{{ locale === 'ar' ? 'EN' : 'ع' }}</span>
          </button>
          <button type="button" class="p-2 text-white" @click="isMenuOpen = !isMenuOpen" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!isMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <nav v-if="isMenuOpen" class="lg:hidden mt-3 pb-2 space-y-1 border-t border-white/10 pt-3">
        <router-link to="/" class="mobile-link" @click="isMenuOpen = false">{{ t('home') }}</router-link>
        <router-link to="/offers" class="mobile-link" @click="isMenuOpen = false">{{ t('offers') }}</router-link>
        <router-link to="/products" class="mobile-link" @click="isMenuOpen = false">{{ t('allProducts') }}</router-link>
        <router-link to="/projects" class="mobile-link" @click="isMenuOpen = false">{{ t('ourProjects') }}</router-link>
        <router-link to="/project-study" class="btn-primary block text-center mt-2" @click="isMenuOpen = false">
          {{ t('projectStudy') }}
        </router-link>
        <router-link to="/gate-machine-study" class="btn-header-secondary block text-center mt-2" @click="isMenuOpen = false">
          {{ t('gateMachineStudy') }}
        </router-link>
        <a
          :href="`https://wa.me/${whatsappNumber}`"
          target="_blank"
          rel="noopener noreferrer"
          class="btn-whatsapp text-center"
        >
          <svg class="wa-icon" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
            <path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/>
          </svg>
          {{ t('whatsapp') }}
        </a>
      </nav>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useLocale } from '@/composables/useLocale'

interface CompanyInfo {
  companyName: string
  tagline: string
  tagline_ar?: string
  contact: { whatsapp: string }
}

const { t, locale, isAr, toggleLocale } = useLocale()
const isMenuOpen = ref(false)
const companyInfo = ref<CompanyInfo | null>(null)

const whatsappNumber = computed(() => companyInfo.value?.contact.whatsapp || '971562566232')

const scrollToSection = (id: string) => {
  const element = document.getElementById(id)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
    isMenuOpen.value = false
  }
}

onMounted(async () => {
  try {
    const res = await fetch('/company-info.json')
    companyInfo.value = await res.json()
  } catch (error) {
    console.error('Error loading company info:', error)
  }
})
</script>
