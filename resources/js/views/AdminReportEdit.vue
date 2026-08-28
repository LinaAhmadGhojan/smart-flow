<template>
  <div dir="rtl">
    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ isNew ? 'تقرير جديد' : 'تعديل التقرير' }}
      </h1>
      <div class="flex items-center gap-3">
        <router-link
          v-if="!isNew && reportId"
          :to="`/admin/reports/${reportId}/view`"
          class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
        >
          معاينة التقرير
        </router-link>
        <router-link to="/admin/reports" class="text-sm text-gray-500 hover:text-blue-600">
          → العودة للتقارير
        </router-link>
      </div>
    </div>

    <div v-if="loading" class="text-center py-16">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <form v-else @submit.prevent="submitForm" class="space-y-6">
      <!-- Meta -->
      <div class="sf-card p-4 sm:p-6 space-y-4">
        <h2 class="font-bold text-gray-900 text-sm border-b pb-2">بيانات الزيارة</h2>
        <div>
          <label class="sf-label">ربط بموعد (اختياري)</label>
          <select v-model="form.appointment_slot_id" @change="onAppointmentChange" class="sf-field">
            <option :value="null">بدون ربط</option>
            <option v-for="a in appointments" :key="a.id" :value="a.id">
              {{ a.date }} — {{ a.customer_name || 'بدون اسم' }} ({{ a.type === 'maintenance' ? 'صيانة' : 'زيارة' }})
            </option>
          </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="sf-label">اسم العميل</label>
            <input v-model="form.client_name" type="text" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">اسم المهندس</label>
            <input v-model="form.engineer_name" type="text" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">تاريخ الزيارة</label>
            <input v-model="form.visit_date" type="date" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">وقت الزيارة</label>
            <input v-model="form.visit_time" type="text" placeholder="10:00 صباحاً" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">نوع الزيارة</label>
            <input v-model="form.visit_type" type="text" placeholder="زيارة دورية / متابعة" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">عنوان التقرير (اختياري)</label>
            <input v-model="form.title" type="text" placeholder="تقرير زيارة موقع" class="sf-field"/>
          </div>
        </div>
      </div>

      <!-- Site info -->
      <div class="sf-card p-4 sm:p-6 space-y-4">
        <h2 class="font-bold text-gray-900 text-sm border-b pb-2">معلومات الموقع</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="sf-label">جهة الاستلام</label>
            <input v-model="form.recipient_entity" type="text" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">اسم الشركة</label>
            <input v-model="form.site_company" type="text" class="sf-field"/>
          </div>
          <div class="sm:col-span-2">
            <label class="sf-label">العنوان</label>
            <input v-model="form.site_address" type="text" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">رقم التواصل</label>
            <input v-model="form.contact_phone" type="text" class="sf-field"/>
          </div>
          <div>
            <label class="sf-label">طريقة التسليم</label>
            <input v-model="form.delivery_method" type="text" placeholder="زيارة ميدانية" class="sf-field"/>
          </div>
          <div class="sm:col-span-2">
            <label class="sf-label">ملاحظات التسليم</label>
            <input v-model="form.delivery_notes" type="text" class="sf-field"/>
          </div>
        </div>
      </div>

      <!-- Content sections -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="sf-card p-4 sm:p-6">
          <label class="sf-label">الأعمال المنفذة</label>
          <p class="text-[11px] text-gray-400 mb-2">سطر لكل نقطة — تظهر كنقاط في PDF</p>
          <textarea v-model="form.executed_works" rows="8" class="sf-field sf-field--textarea leading-relaxed" placeholder="- تركيب لوحات التوزيع&#10;- تمديد الكابلات..."></textarea>
        </div>
        <div class="sf-card p-4 sm:p-6">
          <label class="sf-label">الملاحظات</label>
          <p class="text-[11px] text-gray-400 mb-2">سطر لكل نقطة</p>
          <textarea v-model="form.report_notes" rows="8" class="sf-field sf-field--textarea leading-relaxed" placeholder="- سير العمل جيد&#10;- بعض التعديلات..."></textarea>
        </div>
        <div class="sf-card p-4 sm:p-6 lg:col-span-2">
          <label class="sf-label">التوصيات والإجراءات المطلوبة</label>
          <p class="text-[11px] text-gray-400 mb-2">سطر لكل توصية</p>
          <textarea v-model="form.recommendations" rows="6" class="sf-field sf-field--textarea leading-relaxed" placeholder="- إكمال الأعمال المتبقية&#10;- اختبار الأنظمة..."></textarea>
        </div>
      </div>

      <!-- Photos -->
      <div class="sf-card p-4 sm:p-6">
        <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
          <div>
            <h2 class="font-bold text-gray-900 text-sm">صور الزيارة ({{ totalImageCount }}/5)</h2>
            <p class="text-[11px] text-gray-400 mt-0.5">تظهر مرقّمة 1–5 في عمود يسار التقرير</p>
          </div>
          <label v-if="totalImageCount < 5" class="text-blue-600 text-sm font-medium cursor-pointer hover:text-blue-700">
            + إضافة صور
            <input type="file" accept="image/*" multiple class="hidden" @change="onImagesChange"/>
          </label>
        </div>
        <div v-if="totalImageCount" class="grid grid-cols-2 sm:grid-cols-5 gap-2">
          <div v-for="(img, idx) in existingImages" :key="'existing-' + idx" class="relative aspect-[4/3] rounded-lg overflow-hidden border">
            <span class="absolute top-1 left-1 z-10 w-5 h-5 bg-blue-800 text-white text-[10px] font-bold rounded flex items-center justify-center">{{ idx + 1 }}</span>
            <img :src="mediaUrl(img)" class="w-full h-full object-cover" @error="handleMediaError"/>
            <button type="button" @click="removeExistingImage(idx)" class="absolute top-1 right-1 w-5 h-5 bg-black/60 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs leading-none">×</button>
          </div>
          <div v-for="(prev, idx) in newImagePreviews" :key="'new-' + idx" class="relative aspect-[4/3] rounded-lg overflow-hidden border">
            <span class="absolute top-1 left-1 z-10 w-5 h-5 bg-blue-800 text-white text-[10px] font-bold rounded flex items-center justify-center">{{ existingImages.length + idx + 1 }}</span>
            <img :src="prev" class="w-full h-full object-cover"/>
            <button type="button" @click="removeNewImage(idx)" class="absolute top-1 right-1 w-5 h-5 bg-black/60 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs leading-none">×</button>
          </div>
        </div>
        <p v-else class="text-xs text-gray-400 border border-dashed rounded-lg py-8 text-center">لا يوجد صور بعد</p>
      </div>

      <p v-if="formError" class="text-red-600 text-sm">{{ formError }}</p>

      <div class="sf-actions justify-end border-t pt-4">
        <router-link
          to="/admin/reports"
          class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors text-center"
        >
          إلغاء
        </router-link>
        <button
          type="submit"
          :disabled="submitting"
          class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ submitting ? 'جاري الحفظ...' : 'حفظ التقرير' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface Report {
  id: number
  appointment_slot_id: number | null
  title: string | null
  content: string | null
  images: string[] | null
  client_name: string | null
  engineer_name: string | null
  visit_date: string | null
  visit_time: string | null
  visit_type: string | null
  recipient_entity: string | null
  site_address: string | null
  site_company: string | null
  contact_phone: string | null
  delivery_method: string | null
  delivery_notes: string | null
  executed_works: string | null
  report_notes: string | null
  recommendations: string | null
}

interface AppointmentOption {
  id: number
  date: string
  customer_name: string | null
  engineer_name: string | null
  location: string | null
  start_time: string | null
  type: 'visit' | 'maintenance'
}

const route = useRoute()
const router = useRouter()

const isNew = computed(() => !route.params.id)
const reportId = computed(() => (route.params.id ? Number(route.params.id) : null))

const loading = ref(!isNew.value)
const submitting = ref(false)
const formError = ref('')

const appointments = ref<AppointmentOption[]>([])

const form = reactive({
  appointment_slot_id: null as number | null,
  client_name: '',
  engineer_name: '',
  visit_date: '',
  visit_time: '',
  visit_type: '',
  title: '',
  recipient_entity: '',
  site_address: '',
  site_company: '',
  contact_phone: '',
  delivery_method: '',
  delivery_notes: '',
  executed_works: '',
  report_notes: '',
  recommendations: '',
})

const existingImages = ref<string[]>([])
const newImageFiles = ref<File[]>([])
const newImagePreviews = ref<string[]>([])

const totalImageCount = computed(() => existingImages.value.length + newImageFiles.value.length)

const appendField = (fd: FormData, key: string, val: string) => {
  fd.append(key, val)
}

const fetchAppointments = async () => {
  try {
    const res = await api.get('/admin/appointments', { params: { status: 'booked' } })
    appointments.value = (Array.isArray(res.data) ? res.data : [])
      .slice()
      .sort((a: AppointmentOption, b: AppointmentOption) => (a.date < b.date ? 1 : -1))
  } catch (err) {
    console.error(err)
  }
}

const fetchReport = async () => {
  if (!reportId.value) return
  loading.value = true
  try {
    const res = await api.get(`/admin/reports/${reportId.value}`)
    const r: Report = res.data
    Object.assign(form, {
      appointment_slot_id: r.appointment_slot_id,
      client_name: r.client_name || '',
      engineer_name: r.engineer_name || '',
      visit_date: r.visit_date ? r.visit_date.slice(0, 10) : '',
      visit_time: r.visit_time || '',
      visit_type: r.visit_type || '',
      title: r.title || '',
      recipient_entity: r.recipient_entity || '',
      site_address: r.site_address || '',
      site_company: r.site_company || '',
      contact_phone: r.contact_phone || '',
      delivery_method: r.delivery_method || '',
      delivery_notes: r.delivery_notes || '',
      executed_works: r.executed_works || '',
      report_notes: r.report_notes || r.content || '',
      recommendations: r.recommendations || '',
    })
    existingImages.value = [...(r.images || [])]
  } catch (err) {
    console.error(err)
    formError.value = 'تعذر تحميل بيانات التقرير'
  } finally {
    loading.value = false
  }
}

const onAppointmentChange = () => {
  const appt = appointments.value.find((a) => a.id === form.appointment_slot_id)
  if (!appt) return
  if (!form.client_name) form.client_name = appt.customer_name || ''
  if (!form.engineer_name) form.engineer_name = appt.engineer_name || ''
  if (!form.visit_date) form.visit_date = appt.date
  if (!form.visit_time && appt.start_time) form.visit_time = appt.start_time.slice(0, 5)
  if (!form.recipient_entity) form.recipient_entity = appt.customer_name || ''
  if (!form.site_address) form.site_address = appt.location || ''
  if (!form.site_company) form.site_company = appt.customer_name || ''
  if (!form.visit_type) form.visit_type = appt.type === 'maintenance' ? 'زيارة صيانة' : 'زيارة دورية / متابعة'
}

const onImagesChange = (e: Event) => {
  const input = e.target as HTMLInputElement
  const files = Array.from(input.files || [])
  const remainingSlots = 5 - totalImageCount.value
  const toAdd = files.slice(0, Math.max(0, remainingSlots))
  toAdd.forEach((file) => {
    newImageFiles.value.push(file)
    newImagePreviews.value.push(URL.createObjectURL(file))
  })
  input.value = ''
}

const removeExistingImage = (idx: number) => {
  existingImages.value.splice(idx, 1)
}

const removeNewImage = (idx: number) => {
  newImageFiles.value.splice(idx, 1)
  newImagePreviews.value.splice(idx, 1)
}

const submitForm = async () => {
  formError.value = ''
  submitting.value = true
  try {
    const fd = new FormData()
    if (form.appointment_slot_id) fd.append('appointment_slot_id', String(form.appointment_slot_id))
    appendField(fd, 'client_name', form.client_name)
    appendField(fd, 'engineer_name', form.engineer_name)
    if (form.visit_date) appendField(fd, 'visit_date', form.visit_date)
    appendField(fd, 'visit_time', form.visit_time)
    appendField(fd, 'visit_type', form.visit_type)
    appendField(fd, 'title', form.title)
    appendField(fd, 'recipient_entity', form.recipient_entity)
    appendField(fd, 'site_address', form.site_address)
    appendField(fd, 'site_company', form.site_company)
    appendField(fd, 'contact_phone', form.contact_phone)
    appendField(fd, 'delivery_method', form.delivery_method)
    appendField(fd, 'delivery_notes', form.delivery_notes)
    appendField(fd, 'executed_works', form.executed_works)
    appendField(fd, 'report_notes', form.report_notes)
    appendField(fd, 'content', form.report_notes)
    appendField(fd, 'recommendations', form.recommendations)
    newImageFiles.value.forEach((file) => fd.append('images[]', file))

    if (!isNew.value && reportId.value) {
      fd.append('existing_images', JSON.stringify(existingImages.value))
      await api.post(`/admin/reports/${reportId.value}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    } else {
      await api.post('/admin/reports', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }

    router.push('/admin/reports')
  } catch (err: any) {
    const errors = err.response?.data?.errors
    formError.value = err.response?.data?.message
      || (errors ? Object.values(errors).flat()[0] as string : null)
      || 'حدث خطأ، حاول مرة أخرى'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchAppointments()
  if (!isNew.value) fetchReport()
})
</script>
