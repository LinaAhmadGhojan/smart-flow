<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">إدارة المجموعات | Groups Management</h1>
    <div>
      <div class="mb-6 flex justify-between items-center">
        <h2 class="text-xl font-semibold">All Groups | جميع المجموعات</h2>
        <RouterLink
          to="/admin/groups/new"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
        >
          + Add New Group | إضافة مجموعة
        </RouterLink>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        {{ error }}
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="group in groups"
          :key="group.id"
          class="bg-white rounded-xl shadow overflow-hidden border border-gray-100"
        >
          <div class="h-40 bg-slate-100">
            <img
              v-if="group.image"
              :src="mediaUrl(group.image)"
              :alt="group.name"
              class="w-full h-full object-cover"
              @error="handleMediaError"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-sm">
              No image
            </div>
          </div>
          <div class="p-4">
            <h3 class="font-bold text-gray-900">{{ group.name_ar }}</h3>
            <p class="text-sm text-gray-500 mb-2">{{ group.name }}</p>
            <p class="text-xs text-blue-700 mb-4">{{ group.products_count || 0 }} products</p>
            <div class="flex items-center gap-2">
              <RouterLink
                :to="`/admin/groups/${group.id}`"
                class="text-blue-500 hover:text-blue-700 transition-colors p-1"
                title="تعديل"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </RouterLink>
              <button
                type="button"
                class="text-red-500 hover:text-red-700 transition-colors p-1"
                title="حذف"
                @click="confirmDelete(group)"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف المجموعة؟</p>
          <p class="text-gray-500 text-sm mb-6">{{ deleteTarget.name_ar || deleteTarget.name }}</p>
          <div class="flex gap-3">
            <button type="button" @click="deleteTarget = null" class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">إلغاء</button>
            <button type="button" @click="doDelete" :disabled="deleteLoading" class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-medium">حذف</button>
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

interface Group {
  id: number
  name: string
  name_ar: string
  image?: string | null
  products_count?: number
}

const groups = ref<Group[]>([])
const loading = ref(true)
const error = ref('')
const deleteTarget = ref<Group | null>(null)
const deleteLoading = ref(false)

const fetchGroups = async () => {
  loading.value = true
  try {
    const res = await api.get('/groups')
    groups.value = Array.isArray(res.data) ? res.data : []
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to load groups'
  } finally {
    loading.value = false
  }
}

const confirmDelete = (group: Group) => {
  deleteTarget.value = group
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await api.delete(`/groups/${deleteTarget.value.id}`)
    groups.value = groups.value.filter((g) => g.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (err: any) {
    alert(err.response?.data?.error || 'Failed to delete group')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchGroups)
</script>
