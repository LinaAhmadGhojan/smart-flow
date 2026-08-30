<template>
  <div dir="rtl" class="flex flex-col h-[calc(100vh-4rem)] min-h-[480px] -m-4 sm:-m-6">
    <div class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 bg-white border-b border-gray-200 shrink-0 flex-wrap">
      <div class="flex items-center gap-3 min-w-0">
        <button type="button" class="text-gray-500 hover:text-gray-800 shrink-0" @click="goBack">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
        <div class="min-w-0">
          <h1 class="text-lg font-bold text-gray-900 truncate">تقرير زيارة موقع</h1>
          <p class="text-xs text-gray-500 truncate">{{ reportLabel }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button
          type="button"
          class="border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60"
          :disabled="!htmlContent || pdfLoading"
          @click="exportPdf"
        >
          {{ pdfLoading ? 'جاري التصدير...' : 'تصدير PDF' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex-1 flex items-center justify-center bg-gray-100">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600"></div>
        <p class="text-sm text-gray-500 mt-3">جاري تحميل التقرير...</p>
      </div>
    </div>

    <div v-else-if="error" class="flex-1 flex items-center justify-center bg-gray-100 p-6">
      <div class="bg-white rounded-xl shadow-sm p-8 text-center max-w-md">
        <p class="text-red-600 font-medium mb-4">{{ error }}</p>
        <button type="button" class="text-blue-600 hover:underline text-sm" @click="loadHtml">إعادة المحاولة</button>
      </div>
    </div>

    <iframe
      v-else-if="htmlContent"
      ref="frameRef"
      :srcdoc="htmlContent"
      class="flex-1 w-full border-0 bg-gray-100"
      title="تقرير زيارة موقع"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchAdminHtml } from '@/lib/api'
import { exportReportPdf, reportHtmlPath } from '@/lib/reportPdf'

const route = useRoute()
const router = useRouter()

const reportId = computed(() => String(route.params.id || ''))
const reportLabel = computed(() => `تقرير #${reportId.value}`)

const loading = ref(true)
const pdfLoading = ref(false)
const error = ref('')
const htmlContent = ref('')
const frameRef = ref<HTMLIFrameElement | null>(null)

const loadHtml = async () => {
  loading.value = true
  error.value = ''
  htmlContent.value = ''

  try {
    htmlContent.value = await fetchAdminHtml(reportHtmlPath(reportId.value))
  } catch (err: any) {
    error.value = err.message || 'تعذر تحميل التقرير'
  } finally {
    loading.value = false
  }
}

const exportPdf = async () => {
  pdfLoading.value = true
  try {
    await exportReportPdf(reportId.value, undefined, frameRef.value)
  } catch (err: any) {
    alert(err.message || 'تعذر تصدير PDF')
  } finally {
    pdfLoading.value = false
  }
}

const goBack = () => {
  router.push('/admin/reports')
}

onMounted(loadHtml)
</script>
