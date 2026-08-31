<template>
  <div dir="rtl">
    <div class="sf-page-header">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">مشاريع التنفيذ | CRM Projects</h1>
        <p class="text-sm text-gray-500 mt-1">مشاريع العملاء الداخلية — لا تظهر في الموقع. للموقع الخارجي استخدم «مشاريع الموقع».</p>
        <p class="text-gray-600 mt-1 text-sm">إدارة مشاريع العملاء — العروض والفواتير والملفات</p>
      </div>
      <router-link
        to="/admin/projects/new"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium"
      >
        + مشروع جديد
      </router-link>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-4">
      <div class="flex rounded-lg border border-gray-200 overflow-hidden bg-white">
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium transition-colors"
          :class="tab === 'active' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
          @click="setTab('active')"
        >
          قيد التنفيذ
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200"
          :class="tab === 'completed' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
          @click="setTab('completed')"
        >
          مكتمل
        </button>
      </div>

      <select v-if="tab === 'active'" v-model="statusFilter" class="sf-field !w-auto !py-2 text-sm" @change="fetchProjects">
        <option value="">كل الحالات النشطة</option>
        <option value="draft">مسودة</option>
        <option value="in_progress">قيد التنفيذ</option>
        <option value="on_hold">متوقف</option>
      </select>

      <div class="relative flex-1 min-w-[200px] max-w-sm">
        <input v-model="search" type="text" placeholder="بحث بالمشروع أو العميل أو الموقع..." class="sf-field !py-2 text-sm" @input="debouncedFetch" />
      </div>
    </div>

    <div v-if="loading" class="text-center py-16">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="!projects.length" class="sf-card p-12 text-center text-gray-400">
      لا توجد مشاريع في هذا القسم
    </div>

    <div v-else class="sf-card">
      <div class="sf-table-wrap">
        <table class="sf-table">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-right font-medium text-gray-500">المشروع</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">العميل</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الموقع</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">الحالة</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">عروض</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">فواتير</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in projects" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <p class="font-semibold text-gray-900">{{ p.title_ar || p.title }}</p>
                <p v-if="p.title_ar && p.title" class="text-xs text-gray-500">{{ p.title }}</p>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ p.customer?.name || '—' }}</td>
              <td class="px-4 py-3 text-sm text-gray-500 max-w-[180px] truncate">{{ p.location || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <span class="text-xs px-2 py-1 rounded-full font-medium" :class="statusClass(p.status)">
                  {{ statusLabel(p.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-sm">{{ p.quotations_count ?? 0 }}</td>
              <td class="px-4 py-3 text-center text-sm">{{ p.invoices_count ?? 0 }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                  <router-link :to="`/admin/projects/${p.id}`" class="text-blue-600 hover:text-blue-800 p-1" title="عرض / تعديل">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </router-link>
                  <button v-if="p.status !== 'completed'" type="button" class="text-red-500 hover:text-red-700 p-1" title="حذف" @click="confirmDelete(p)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <p class="text-lg font-bold mb-2">حذف المشروع؟</p>
          <p class="text-gray-500 text-sm mb-6">{{ deleteTarget.title_ar || deleteTarget.title }}</p>
          <div class="flex gap-3">
            <button type="button" class="flex-1 border py-2.5 rounded-lg" @click="deleteTarget = null">إلغاء</button>
            <button type="button" class="flex-1 bg-red-600 text-white py-2.5 rounded-lg" @click="doDelete">حذف</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/lib/api'

interface ProjectRow {
  id: number
  title: string
  title_ar: string
  location: string | null
  status: string
  customer?: { name: string } | null
  quotations_count?: number
  invoices_count?: number
}

const projects = ref<ProjectRow[]>([])
const loading = ref(true)
const tab = ref<'active' | 'completed'>('active')
const statusFilter = ref('')
const search = ref('')
const deleteTarget = ref<ProjectRow | null>(null)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const statusLabel = (s: string) => ({
  draft: 'مسودة',
  in_progress: 'قيد التنفيذ',
  on_hold: 'متوقف',
  completed: 'مكتمل',
  cancelled: 'ملغي',
}[s] || s)

const statusClass = (s: string) => ({
  draft: 'bg-gray-100 text-gray-700',
  in_progress: 'bg-blue-100 text-blue-700',
  on_hold: 'bg-amber-100 text-amber-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
}[s] || 'bg-gray-100 text-gray-700')

const fetchProjects = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/projects', {
      params: {
        tab: tab.value,
        status: statusFilter.value || undefined,
        q: search.value.trim() || undefined,
      },
    })
    projects.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const setTab = (t: 'active' | 'completed') => {
  tab.value = t
  statusFilter.value = ''
  fetchProjects()
}

const debouncedFetch = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchProjects, 300)
}

const confirmDelete = (p: ProjectRow) => { deleteTarget.value = p }

const doDelete = async () => {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/projects/${deleteTarget.value.id}`)
    projects.value = projects.value.filter((p) => p.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch {
    alert('تعذر حذف المشروع')
  }
}

onMounted(fetchProjects)
</script>
