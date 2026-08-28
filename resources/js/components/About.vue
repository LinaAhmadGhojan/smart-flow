<template>
  <section id="about" class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <p class="sf-eyebrow">SmartFlow</p>
        <h2 class="sf-heading">{{ t('aboutUs') }}</h2>
      </div>

      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div :class="isAr ? 'text-right' : 'text-left'">
          <h3 class="text-2xl md:text-3xl font-bold text-[var(--sf-navy)] mb-5">
            {{
              isAr
                ? (companyInfo?.companyname_ar || 'سمارت فلو للتجارة العامة')
                : (companyInfo?.companyName || 'SmartFlow General Trading')
            }}
          </h3>
          <p class="text-lg text-slate-700 leading-relaxed mb-8">
            {{
              isAr
                ? (companyInfo?.aboutAr || 'نحن شركة رائدة في مجال توفير الحلول الذكية والأنظمة الكهربائية المتطورة. نفخر بتقديم أفضل المنتجات والخدمات لعملائنا في دولة الإمارات العربية المتحدة.')
                : (companyInfo?.aboutEn || 'We are a leading company in providing smart solutions and advanced electrical systems. We pride ourselves on delivering the best products and services to our clients in the United Arab Emirates.')
            }}
          </p>

          <div class="grid grid-cols-3 gap-4">
            <div class="stat-tile">
              <div class="stat-tile__value">15+</div>
              <div class="stat-tile__label">{{ t('yearsExp') }}</div>
            </div>
            <div class="stat-tile">
              <div class="stat-tile__value">500+</div>
              <div class="stat-tile__label">{{ t('happyClients') }}</div>
            </div>
            <div class="stat-tile">
              <div class="stat-tile__value">1000+</div>
              <div class="stat-tile__label">{{ t('projectsDone') }}</div>
            </div>
          </div>
        </div>

        <div class="about-visual">
          <img
            src="/logo.jpeg"
            alt="SmartFlow"
            class="about-visual__logo"
          />
          <div class="about-visual__glow" aria-hidden="true" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLocale } from '@/composables/useLocale'

interface CompanyInfo {
  companyName?: string
  companyname_ar?: string
  aboutAr?: string
  aboutEn?: string
}

const { t, isAr } = useLocale()
const companyInfo = ref<CompanyInfo | null>(null)

onMounted(async () => {
  try {
    const res = await fetch('/company-info.json')
    companyInfo.value = await res.json()
  } catch (error) {
    console.error('Error loading company info:', error)
  }
})
</script>
