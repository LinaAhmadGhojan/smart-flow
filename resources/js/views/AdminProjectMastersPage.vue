<template>
  <div dir="rtl">
    <div class="sf-page-header mb-6">
      <h1 class="text-2xl font-bold text-gray-900">مشاريع الموقع | Website Projects</h1>
      <RouterLink
        to="/admin/project-masters/new"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium transition-colors"
      >
        + إضافة مشروع للموقع
      </RouterLink>
    </div>

    <p class="text-sm text-gray-500 mb-4">
      هذه المشاريع تظهر في الصفحة الرئيسية للموقع فقط. مشاريع التنفيذ (CRM) منفصلة ولا تُعرض للزوار.
    </p>

    <div class="mb-4 flex flex-wrap gap-2">
      <button
        v-for="f in filters"
        :key="f.value"
        type="button"
        @click="activeFilter = f.value; fetchItems()"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          activeFilter === f.value ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border hover:bg-gray-50',
        ]"
      >{{ f.label }}</button>
    </div>

    <div class="mb-4 relative max-w-md">
      <input
        v-model="search"
        type="text"
        placeholder="بحث بالعنوان أو الموقع..."
        class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
        @input="debouncedFetch"
      />
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="!items.length" class="sf-card p-12 text-center text-gray-400">
      لا توجد مشاريع للموقع
    </div>

    <div v-else class="sf-card">
      <div class="sf-table-wrap">
        <table class="sf-table">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الصورة</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">العنوان</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الموقع</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">ترتيب</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">مميز</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">ظاهر بالموقع</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">إجراءات</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <img
                  v-if="cover(item)"
                  :src="mediaUrl(cover(item)!)"
                  :alt="item.title_ar"
                  class="h-12 w-16 object-cover rounded border border-gray-100"
                  @error="handleMediaError"
                />
                <div v-else class="h-12 w-16 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">—</div>
              </td>
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ item.title_ar }}</div>
                <div class="text-xs text-gray-500">{{ item.title }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ item.location || '—' }}</td>
              <td class="px-4 py-3 text-center text-sm">{{ item.order ?? 0 }}</td>
              <td class="px-4 py-3 text-center">
                <span v-if="item.is_featured" class="text-amber-600 text-xs font-bold">★ مميز</span>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-4 py-3 text-center">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    class="w-4 h-4"
                    :checked="!!item.is_visible"
                    @change="toggleVisibility(item, ($event.target as HTMLInputElement).checked)"
                  />
                  <span
                    class="text-xs font-medium"
                    :class="item.is_visible ? 'text-green-700' : 'text-gray-400'"
                  >{{ item.is_visible ? 'ظاهر' : 'مخفي' }}</span>
                </label>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                  <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg bg-gray-50 text-gray-700 text-sm hover:bg-gray-100 border border-gray-200"
                    @click="openDetails(item)"
                  >عرض التفاصيل</button>
                  <RouterLink
                    :to="`/admin/project-masters/${item.id}`"
                    class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-sm hover:bg-blue-100"
                  >تعديل</RouterLink>
                  <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-sm hover:bg-red-100"
                    @click="confirmDelete(item)"
                  >حذف</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="detailsOpen" class="sf-modal-backdrop" dir="rtl" @click.self="detailsOpen = false">
        <div class="sf-modal-panel max-w-lg w-full max-h-[85vh] overflow-y-auto text-right">
          <div class="flex items-start justify-between gap-3 mb-4">
            <h2 class="text-lg font-bold text-gray-900">تفاصيل المشروع</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 text-xl leading-none" @click="detailsOpen = false">×</button>
          </div>

          <div v-if="detailsLoading" class="py-10 text-center text-gray-500">جاري التحميل...</div>

          <div v-else-if="detailsItem" class="space-y-4 text-sm">
            <div>
              <p class="text-xs text-gray-500 mb-1">العنوان (عربي)</p>
              <p class="font-semibold text-gray-900">{{ detailsItem.title_ar || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Title (English)</p>
              <p class="text-gray-800">{{ detailsItem.title || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">الموقع</p>
              <p class="text-gray-800">{{ detailsItem.location || '—' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-xs text-gray-500 mb-1">الترتيب</p>
                <p class="text-gray-800">{{ detailsItem.order ?? 0 }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">الظهور</p>
                <p class="text-gray-800">{{ detailsItem.is_visible ? 'ظاهر بالموقع' : 'مخفي' }}</p>
              </div>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">مميز</p>
              <p class="text-gray-800">{{ detailsItem.is_featured ? 'نعم' : 'لا' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">الوصف (عربي)</p>
              <p class="text-gray-800 whitespace-pre-wrap leading-relaxed">{{ detailsItem.description_ar || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Description (English)</p>
              <p class="text-gray-800 whitespace-pre-wrap leading-relaxed">{{ detailsItem.description || '—' }}</p>
            </div>
            <div v-if="detailsItem.files?.length">
              <p class="text-xs text-gray-500 mb-2">الملفات / الصور</p>
              <ul class="space-y-1">
                <li v-for="f in detailsItem.files" :key="f.id" class="text-gray-700">
                  • {{ f.label }} <span class="text-gray-400">({{ f.kind }})</span>
                </li>
              </ul>
            </div>
          </div>

          <div class="mt-6 flex gap-2 justify-end">
            <button type="button" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50" @click="detailsOpen = false">
              إغلاق
            </button>
            <RouterLink
              v-if="detailsItem"
              :to="`/admin/project-masters/${detailsItem.id}`"
              class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
              @click="detailsOpen = false"
            >
              تعديل
            </RouterLink>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface MasterFile {
  id: number
  label: string
  path: string
  kind: string
}

interface ProjectMaster {
  id: number
  title: string
  title_ar: string
  description?: string
  description_ar?: string
  location?: string | null
  order?: number
  is_featured: boolean
  is_visible: boolean
  media_url?: string | null
  files?: MasterFile[]
}

const items = ref<ProjectMaster[]>([])
const loading = ref(true)
const search = ref('')
const activeFilter = ref<'all' | 'visible' | 'hidden'>('all')
const detailsOpen = ref(false)
const detailsLoading = ref(false)
const detailsItem = ref<ProjectMaster | null>(null)
let searchTimer: ReturnType<typeof setTimeout> | null = null

const filters = [
  { value: 'all' as const, label: 'الكل' },
  { value: 'visible' as const, label: 'ظاهر بالموقع' },
  { value: 'hidden' as const, label: 'مخفي' },
]

const cover = (item: ProjectMaster) => {
  const img = item.files?.find((f) => f.kind === 'image')
  return img?.path || item.media_url || null
}

const fetchItems = async () => {
  loading.value = true
  try {
    const params: Record<string, string> = {}
    if (search.value.trim()) params.q = search.value.trim()
    if (activeFilter.value === 'visible') params.visible = '1'
    if (activeFilter.value === 'hidden') params.visible = '0'
    const { data } = await api.get('/admin/project-masters', { params })
    items.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error(e)
    items.value = []
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(fetchItems, 300)
}

const openDetails = async (item: ProjectMaster) => {
  detailsOpen.value = true
  detailsLoading.value = true
  detailsItem.value = null
  try {
    const { data } = await api.get(`/admin/project-masters/${item.id}`)
    detailsItem.value = data
  } catch (e) {
    console.error(e)
    alert('تعذر تحميل التفاصيل')
    detailsOpen.value = false
  } finally {
    detailsLoading.value = false
  }
}

const toggleVisibility = async (item: ProjectMaster, visible: boolean) => {
  try {
    const { data } = await api.patch(`/admin/project-masters/${item.id}/visibility`, { is_visible: visible })
    item.is_visible = !!data.is_visible
  } catch (e) {
    console.error(e)
    alert('تعذر تحديث الظهور')
    fetchItems()
  }
}

const confirmDelete = async (item: ProjectMaster) => {
  if (!confirm(`حذف "${item.title_ar}" من مشاريع الموقع؟`)) return
  try {
    await api.delete(`/project-masters/${item.id}`)
    items.value = items.value.filter((i) => i.id !== item.id)
  } catch (e) {
    console.error(e)
    alert('تعذر الحذف')
  }
}

onMounted(fetchItems)
</script>
