<template>
  <div class="admin-shell">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/40 z-30 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="admin-sidebar"
      :class="{ 'is-open': sidebarOpen }"
    >
      <div class="px-5 py-5 flex items-center gap-3 border-b border-white/10">
        <img src="/logo.svg" alt="SmartFlow" class="w-9 h-9 rounded-lg bg-white/10 p-1" @error="onLogoError" />
        <div class="leading-tight">
          <p class="text-white font-bold text-base">SmartFlow</p>
          <p class="text-white/50 text-xs">لوحة التحكم</p>
        </div>
      </div>

      <!-- Quick create -->
      <div class="px-4 pt-4 relative">
        <button
          type="button"
          @click="showCreateMenu = !showCreateMenu"
          class="w-full flex items-center justify-center gap-2 bg-[var(--sf-accent)] hover:brightness-110 text-white text-sm font-semibold py-2.5 rounded-xl transition-all"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          إضافة جديد
        </button>

        <div
          v-if="showCreateMenu"
          class="absolute right-4 left-4 mt-2 bg-white rounded-xl shadow-2xl py-2 z-40 text-sm"
          @click="showCreateMenu = false"
        >
          <RouterLink to="/admin/products/new" class="quick-create-item">+ منتج جديد</RouterLink>
          <RouterLink to="/admin/categories/new" class="quick-create-item">+ فئة جديدة</RouterLink>
          <RouterLink to="/admin/groups/new" class="quick-create-item">+ مجموعة جديدة</RouterLink>
          <RouterLink to="/admin/projects/new" class="quick-create-item">+ مشروع تنفيذ (CRM)</RouterLink>
          <RouterLink to="/admin/project-masters/new" class="quick-create-item">+ مشروع للموقع</RouterLink>
          <RouterLink to="/admin/quotations/new" class="quick-create-item">+ عرض سعر جديد</RouterLink>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="nav-item"
          :class="{ 'nav-item--active': isActive(item.to) }"
          @click="sidebarOpen = false"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
          </svg>
          <span class="flex-1">{{ item.label }}</span>
          <span
            v-if="item.to === '/admin/study-requests' && newTotal > 0"
            class="nav-badge"
          >
            {{ newTotal > 99 ? '99+' : newTotal }}
          </span>
        </RouterLink>
      </nav>

      <div class="px-3 py-4 border-t border-white/10 space-y-1">
        <a href="/" class="nav-item">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span>الموقع الرئيسي</span>
        </a>
        <button type="button" @click="handleLogout" class="nav-item nav-item--danger w-full text-right">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>تسجيل الخروج</span>
        </button>
      </div>
    </aside>

    <!-- Main column -->
    <div class="admin-main">
      <!-- Top bar -->
      <header class="admin-topbar">
        <button type="button" class="lg:hidden text-gray-500" @click="sidebarOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="hidden sm:flex items-center flex-1 max-w-sm relative">
          <svg class="w-4 h-4 absolute right-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
          </svg>
          <input
            type="text"
            placeholder="بحث..."
            class="w-full bg-gray-100 rounded-lg pr-9 pl-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[var(--sf-accent)]/30"
            disabled
          />
        </div>

        <div class="flex-1 sm:hidden"></div>

        <div class="flex items-center gap-4">
          <div class="relative" ref="notifWrap">
            <button
              type="button"
              class="relative text-gray-500 hover:text-gray-700 p-1"
              title="الإشعارات"
              @click="togglePanel"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span v-if="newTotal > 0" class="notif-dot">{{ newTotal > 9 ? '9+' : newTotal }}</span>
            </button>

            <div
              v-if="panelOpen"
              class="notif-panel"
              dir="rtl"
            >
              <div class="notif-panel-head">
                <p class="font-bold text-gray-900">الإشعارات</p>
                <span v-if="newTotal > 0" class="text-xs font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                  {{ newTotal }} جديد
                </span>
              </div>

              <div v-if="browserPermission !== 'granted'" class="notif-permission">
                <p class="text-xs text-gray-600 mb-2">فعّل إشعارات المتصفح لتصلك تنبيهات حتى وأنت خارج الداشبورد.</p>
                <button
                  type="button"
                  class="w-full text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium"
                  @click="enableBrowserNotifications"
                >
                  تفعيل الإشعارات
                </button>
              </div>

              <div v-if="latest.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                لا توجد طلبات جديدة
              </div>
              <ul v-else class="notif-list">
                <li v-for="item in latest" :key="`${item.kind}-${item.id}`">
                  <RouterLink
                    to="/admin/study-requests"
                    class="notif-item"
                    @click="closePanel"
                  >
                    <span class="notif-kind" :class="item.kind === 'gate' ? 'notif-kind--gate' : 'notif-kind--project'">
                      {{ kindLabel(item.kind) }}
                    </span>
                    <span class="font-semibold text-gray-900">{{ item.customer_name }}</span>
                    <span class="text-xs text-gray-500">{{ item.customer_phone }}</span>
                  </RouterLink>
                </li>
              </ul>

              <RouterLink
                v-if="newTotal > 0"
                to="/admin/study-requests"
                class="notif-footer"
                @click="closePanel"
              >
                عرض كل الطلبات ({{ newTotal }})
              </RouterLink>
            </div>
          </div>

          <div class="relative">
            <button type="button" @click="showUserMenu = !showUserMenu" class="flex items-center gap-2">
              <div class="w-9 h-9 rounded-full bg-[var(--sf-navy)] text-white flex items-center justify-center text-sm font-bold">
                {{ adminInitial }}
              </div>
              <span class="hidden md:block text-sm font-medium text-gray-700">{{ adminEmail }}</span>
              <svg class="w-4 h-4 text-gray-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div
              v-if="showUserMenu"
              class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-2xl py-2 z-40 text-sm"
              @click="showUserMenu = false"
            >
              <RouterLink to="/admin/settings" class="quick-create-item">إعدادات الشركة</RouterLink>
              <a href="/" class="quick-create-item">عرض الموقع</a>
              <button type="button" @click="handleLogout" class="quick-create-item w-full text-right text-red-600">
                تسجيل الخروج
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="admin-content">
        <RouterView :key="route.fullPath" />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import { useAdminNotifications } from '@/composables/useAdminNotifications'

const route = useRoute()
const router = useRouter()

const {
  newTotal,
  latest,
  panelOpen,
  browserPermission,
  kindLabel,
  startPolling,
  stopPolling,
  requestBrowserPermission,
  togglePanel,
  closePanel,
} = useAdminNotifications()

const sidebarOpen = ref(false)
const showCreateMenu = ref(false)
const showUserMenu = ref(false)
const notifWrap = ref<HTMLElement | null>(null)

const enableBrowserNotifications = async () => {
  await requestBrowserPermission()
}

const onDocumentClick = (e: MouseEvent) => {
  if (!panelOpen.value) return
  if (notifWrap.value && !notifWrap.value.contains(e.target as Node)) {
    closePanel()
  }
}

onMounted(() => {
  startPolling()
  document.addEventListener('click', onDocumentClick)
})

onUnmounted(() => {
  stopPolling()
  document.removeEventListener('click', onDocumentClick)
})

const navItems = [
  { to: '/admin/dashboard', label: 'الرئيسية', icon: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11l1 1v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { to: '/admin/products', label: 'المنتجات', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
  { to: '/admin/categories', label: 'الفئات', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
  { to: '/admin/groups', label: 'المجموعات', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
  { to: '/admin/offers', label: 'العروض', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { to: '/admin/projects', label: 'مشاريع التنفيذ', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  { to: '/admin/project-masters', label: 'مشاريع الموقع', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
  { to: '/admin/payments', label: 'الدفعات', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
  { to: '/admin/reviews', label: 'التقييمات', icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
  { to: '/admin/appointments', label: 'المواعيد', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
  { to: '/admin/study-requests', label: 'طلبات الدراسة', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { to: '/admin/customers', label: 'العملاء', icon: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 10-3.998-4.318A4 4 0 0018 12.13z' },
  { to: '/admin/engineers', label: 'المهندسون', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { to: '/admin/reports', label: 'التقارير', icon: 'M9 17v-6h6v6m-9 4h12a2 2 0 002-2V6.414a2 2 0 00-.586-1.414l-2.414-2.414A2 2 0 0015.586 2H6a2 2 0 00-2 2v13a2 2 0 002 2zm3-14h4v3H9V7z' },
  { to: '/admin/quotations', label: 'عروض الأسعار', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { to: '/admin/invoices', label: 'الفواتير', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
  { to: '/admin/delivery-notes', label: 'Delivery Notes', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
  { to: '/admin/settings', label: 'الإعدادات', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
]

const isActive = (to: string) => route.path === to || route.path.startsWith(to + '/')

const adminEmail = computed(() => sessionStorage.getItem('adminEmail') || 'المدير')
const adminInitial = computed(() => adminEmail.value.charAt(0).toUpperCase())

const onLogoError = (e: Event) => {
  (e.target as HTMLImageElement).style.display = 'none'
}

const handleLogout = () => {
  sessionStorage.removeItem('adminLoggedIn')
  sessionStorage.removeItem('authToken')
  sessionStorage.removeItem('adminEmail')
  router.push('/admin')
}
</script>

<style scoped>
.admin-shell {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

.admin-sidebar {
  width: 260px;
  flex-shrink: 0;
  background: #10131c;
  display: flex;
  flex-direction: column;
  position: fixed;
  right: 0;
  top: 0;
  bottom: 0;
  z-index: 40;
  transform: translateX(100%);
  transition: transform 0.25s ease;
}

.admin-sidebar.is-open {
  transform: translateX(0);
}

@media (min-width: 1024px) {
  .admin-sidebar {
    transform: translateX(0);
  }
}

.admin-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

@media (min-width: 1024px) {
  .admin-main {
    margin-right: 260px;
  }
}

.admin-topbar {
  height: 64px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0 1.25rem;
  position: sticky;
  top: 0;
  z-index: 20;
}

.admin-content {
  flex: 1;
  min-width: 0;
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  padding: 1rem;
}

.admin-content--flush {
  padding: 0.5rem;
}

@media (min-width: 640px) {
  .admin-content {
    padding: 1.25rem;
  }
  .admin-content--flush {
    padding: 0.5rem;
  }
}

@media (min-width: 768px) {
  .admin-content {
    padding: 2rem;
  }
  .admin-content--flush {
    padding: 0.5rem;
  }
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.65rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.65);
  transition: all 0.15s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}

.nav-item--active {
  background: var(--sf-accent, #1d4f91);
  color: #fff;
}

.nav-item--danger:hover {
  background: rgba(220, 38, 38, 0.15);
  color: #fca5a5;
}

.quick-create-item {
  display: block;
  padding: 0.5rem 1rem;
  color: #374151;
  transition: background 0.15s ease;
}

.quick-create-item:hover {
  background: #f3f4f6;
}

.nav-badge {
  min-width: 1.25rem;
  height: 1.25rem;
  padding: 0 0.35rem;
  border-radius: 9999px;
  background: #ef4444;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  line-height: 1.25rem;
  text-align: center;
}

.notif-dot {
  position: absolute;
  top: -2px;
  left: -2px;
  min-width: 1.1rem;
  height: 1.1rem;
  padding: 0 0.25rem;
  border-radius: 9999px;
  background: #ef4444;
  color: #fff;
  font-size: 0.6rem;
  font-weight: 700;
  line-height: 1.1rem;
  text-align: center;
  border: 2px solid #fff;
}

.notif-panel {
  position: absolute;
  left: 0;
  top: calc(100% + 0.5rem);
  width: min(20rem, calc(100vw - 2rem));
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.85rem;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
  z-index: 50;
  overflow: hidden;
}

.notif-panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.notif-permission {
  padding: 0.75rem 1rem;
  background: #f0fdf4;
  border-bottom: 1px solid #dcfce7;
}

.notif-list {
  max-height: 18rem;
  overflow-y: auto;
}

.notif-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  transition: background 0.15s ease;
}

.notif-item:hover {
  background: #f9fafb;
}

.notif-kind {
  display: inline-block;
  width: fit-content;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.1rem 0.45rem;
  border-radius: 9999px;
}

.notif-kind--project {
  background: #dbeafe;
  color: #1d4ed8;
}

.notif-kind--gate {
  background: #ccfbf1;
  color: #0f766e;
}

.notif-footer {
  display: block;
  text-align: center;
  padding: 0.75rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--sf-accent, #1d4f91);
  background: #f9fafb;
}

.notif-footer:hover {
  background: #f3f4f6;
}
</style>
