<template>
  <div>
    <!-- Welcome header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">مرحباً بك 👋</h1>
        <p class="text-gray-500 mt-1">{{ todayLabel }}</p>
      </div>
    </div>

    <!-- New requests alert -->
    <div
      v-if="newRequestsTotal > 0"
      class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex flex-wrap items-center justify-between gap-3"
    >
      <p class="text-sm text-amber-900">
        <strong>{{ newRequestsTotal }}</strong> طلب جديد بانتظار المتابعة
        <span v-if="newGateStudies > 0" class="text-amber-700"> ({{ newGateStudies }} ماكينة باب)</span>
      </p>
      <RouterLink
        to="/admin/study-requests"
        class="text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg"
      >
        عرض الطلبات
      </RouterLink>
    </div>

    <!-- Quick stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 flex items-center gap-3 sm:gap-4 min-w-0">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="stat.bg">
          <svg class="w-6 h-6" :class="stat.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="stat.icon" />
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ stat.loading ? '…' : stat.value }}</p>
          <p class="text-sm text-gray-500">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Modules grid -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4">إدارة المحتوى</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <RouterLink
        v-for="card in cards"
        :key="card.to"
        :to="card.to"
        class="group bg-white rounded-2xl shadow-sm hover:shadow-lg p-6 transition-all border border-transparent hover:border-gray-100"
      >
        <div class="flex items-center gap-4">
          <div class="p-3 rounded-xl transition-transform group-hover:scale-105" :class="card.bg">
            <svg class="w-7 h-7" :class="card.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="card.icon" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900">{{ card.title }}</h3>
            <p class="text-gray-400 text-sm">{{ card.subtitle }}</p>
          </div>
        </div>
        <p class="mt-4 text-gray-600 text-sm">{{ card.description }}</p>
      </RouterLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/api'

const newRequestsTotal = ref(0)
const newGateStudies = ref(0)

const todayLabel = new Date().toLocaleDateString('ar-AE', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric',
})

const stats = reactive([
  { label: 'المنتجات', value: 0, loading: true, bg: 'bg-blue-100', text: 'text-blue-600', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', key: 'products' },
  { label: 'الفئات', value: 0, loading: true, bg: 'bg-green-100', text: 'text-green-600', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', key: 'categories' },
  { label: 'طلبات الدراسة', value: 0, loading: true, bg: 'bg-cyan-100', text: 'text-cyan-600', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', key: 'studyRequests' },
  { label: 'مواعيد اليوم', value: 0, loading: true, bg: 'bg-teal-100', text: 'text-teal-600', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', key: 'appointments' },
])

const setStat = (key: string, value: number) => {
  const stat = stats.find(s => s.key === key)
  if (stat) {
    stat.value = value
    stat.loading = false
  }
}

const loadStats = async () => {
  try {
    const res = await api.get('/products')
    const list = res.data.products || res.data
    setStat('products', Array.isArray(list) ? list.length : 0)
  } catch { setStat('products', 0) }

  try {
    const res = await api.get('/categories')
    setStat('categories', Array.isArray(res.data) ? res.data.length : 0)
  } catch { setStat('categories', 0) }

  try {
    const res = await api.get('/admin/notifications/summary')
    const total = Number(res.data?.new_total) || 0
    newRequestsTotal.value = total
    newGateStudies.value = Number(res.data?.new_gate_studies) || 0
    setStat('studyRequests', total)
  } catch { setStat('studyRequests', 0) }

  try {
    const today = new Date().toISOString().slice(0, 10)
    const res = await api.get('/admin/appointments', { params: { from: today, to: today } })
    const list = Array.isArray(res.data) ? res.data : (res.data.data || [])
    setStat('appointments', list.length)
  } catch { setStat('appointments', 0) }
}

const cards = [
  {
    to: '/admin/products', title: 'المنتجات', subtitle: 'Products',
    description: 'إدارة المنتجات والأسعار', bg: 'bg-blue-100', text: 'text-blue-600',
    icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  },
  {
    to: '/admin/offers', title: 'العروض', subtitle: 'Offers',
    description: 'إدارة العروض والخصومات', bg: 'bg-red-100', text: 'text-red-600',
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  {
    to: '/admin/categories', title: 'الفئات', subtitle: 'Categories',
    description: 'إدارة فئات المنتجات', bg: 'bg-green-100', text: 'text-green-600',
    icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
  },
  {
    to: '/admin/groups', title: 'المجموعات', subtitle: 'Groups',
    description: 'إدارة مجموعات المنتجات بالصور', bg: 'bg-indigo-100', text: 'text-indigo-600',
    icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
  },
  {
    to: '/admin/projects', title: 'المشاريع', subtitle: 'Projects',
    description: 'إدارة مشاريع الشركة', bg: 'bg-orange-100', text: 'text-orange-600',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  },
  {
    to: '/admin/reviews', title: 'التقييمات', subtitle: 'Reviews',
    description: 'إدارة تقييمات العملاء وآرائهم', bg: 'bg-yellow-100', text: 'text-yellow-500',
    icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
  },
  {
    to: '/admin/appointments', title: 'المواعيد', subtitle: 'Appointments',
    description: 'إدارة جدول المواعيد المتاحة والمحجوزة', bg: 'bg-teal-100', text: 'text-teal-600',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  },
  {
    to: '/admin/study-requests', title: 'طلبات الدراسة', subtitle: 'Study & Gate Requests',
    description: 'استبيانات دراسة المشروع وطلبات ماكينة الباب', bg: 'bg-cyan-100', text: 'text-cyan-600',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  },
  {
    to: '/admin/settings', title: 'الإعدادات', subtitle: 'Settings',
    description: 'معلومات التواصل والشركة', bg: 'bg-purple-100', text: 'text-purple-600',
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
  },
]

onMounted(() => {
  loadStats()
})
</script>
