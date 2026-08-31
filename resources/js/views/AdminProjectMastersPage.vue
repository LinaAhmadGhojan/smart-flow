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
                <div class="flex items-center justify-center gap-2">
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface MasterFile {
  id: number
  path: string
  kind: string
}

interface ProjectMaster {
  id: number
  title: string
  title_ar: string
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
