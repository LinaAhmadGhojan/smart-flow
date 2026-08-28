<template>
  <footer class="bg-blue-900 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid md:grid-cols-3 gap-8">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <img
              :src="'/logo.jpeg'"
              alt="SmartFlow Logo"
              class="h-12 w-12 object-contain bg-white rounded-lg p-1"
            />
            <div>
              <h3 class="text-xl font-bold">{{ companyInfo?.companyName || 'SMARTFLOW' }}</h3>
              <p class="text-sm text-blue-200">{{ companyInfo?.tagline || 'Your Home is Safe With Us' }}</p>
            </div>
          </div>
          <p class="text-blue-100">
            {{ companyInfo?.footerDescAr || 'شريكك الموثوق في الحلول الذكية' }}
          </p>
        </div>

        <div>
          <h4 class="text-lg font-bold mb-1">روابط سريعة | Quick Links</h4>
          <ul class="space-y-2">
            <li>
              <button @click="scrollToSection('home')" class="text-blue-100 hover:text-white transition-colors">
                الرئيسية | Home
              </button>
            </li>
            <li>
              <button @click="scrollToSection('products')" class="text-blue-100 hover:text-white transition-colors">
                المنتجات | Products
              </button>
            </li>
            <li>
              <button @click="scrollToSection('contact')" class="text-blue-100 hover:text-white transition-colors">
                اتصل بنا | Contact
              </button>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-lg font-bold mb-1">تواصل معنا | Contact Us</h4>
          <div class="space-y-3 text-blue-100">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              <a :href="`tel:${companyInfo?.contact.phone}`" class="hover:text-white">
                {{ companyInfo?.contact.phone || '+971 56 256 6232' }}
              </a>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <a :href="`mailto:${companyInfo?.contact.email}`" class="hover:text-white">
                {{ companyInfo?.contact.email || 'info@smartflow.ae' }}
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="border-t border-blue-800 mt-8 pt-8 text-center text-blue-200">
        <p>© {{ currentYear }} {{ companyInfo?.companyName || 'SMARTFLOW' }}. All rights reserved.</p>
        <p class="text-sm mt-2">جميع الحقوق محفوظة</p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface CompanyInfo {
  companyName: string
  tagline: string
  footerDescAr?: string
  contact: {
    phone: string
    email: string
  }
}

const companyInfo = ref<CompanyInfo | null>(null)

const currentYear = computed(() => new Date().getFullYear())

const scrollToSection = (id: string) => {
  const element = document.getElementById(id)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
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
