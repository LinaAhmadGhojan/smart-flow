<template>
  <div class="min-h-screen sf-section">
    <Header />

    <div class="pt-28 pb-16">
      <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl mx-auto">
          <router-link to="/" class="inline-flex items-center gap-2 text-[var(--sf-accent)] hover:text-[var(--sf-navy)] mb-6 text-sm font-semibold transition-colors">
            <span aria-hidden="true">→</span>
            العودة للرئيسية | Back to Home
          </router-link>

          <div class="text-center">
            <p class="sf-eyebrow">دراسة مشروع | Project Study</p>
            <h1 class="sf-heading">استبيان متطلبات أنظمة المنزل الذكي</h1>
            <p class="sf-subheading mt-3">
              عبّي البيانات التالية وفريقنا رح يتواصل معك لدراسة مشروعك بأفضل شكل
            </p>
          </div>
        </div>

        <!-- Success state (under construction submissions) -->
        <div v-if="submitted" class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8 text-center">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-[var(--sf-navy)] mb-2">تم استلام طلبك بنجاح!</h2>
          <p class="text-gray-600 mb-6">سنتواصل معك قريباً لمتابعة تفاصيل مشروعك.</p>
          <router-link to="/" class="btn-primary">
            العودة للرئيسية
          </router-link>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="handleSubmit" class="max-w-3xl mx-auto space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ error }}
          </div>

          <!-- 1. House status -->
          <section class="bg-white rounded-2xl shadow p-6">
            <h3 class="section-title">1. حالة المنزل</h3>
            <p class="text-sm text-gray-500 mb-3">هل المنزل قيد الإنشاء أم قائم بالفعل؟</p>
            <div class="flex flex-col sm:flex-row gap-3">
              <label class="option-card" :class="{ 'option-card--active': form.house_status === 'under_construction' }">
                <input
                  type="radio"
                  value="under_construction"
                  v-model="form.house_status"
                  class="sr-only"
                  @change="handleHouseStatusChange"
                />
                قيد الإنشاء
              </label>
              <label class="option-card" :class="{ 'option-card--active': form.house_status === 'existing' }">
                <input
                  type="radio"
                  value="existing"
                  v-model="form.house_status"
                  class="sr-only"
                  @change="handleHouseStatusChange"
                />
                قائم بالفعل
              </label>
            </div>

            <div v-if="form.house_status === 'existing'" class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-900">
              <p class="mb-3">
                بما إن المنزل قائم بالفعل، تم تحويلك إلى واتساب للتواصل المباشر مع فريقنا وتحديد موعد الزيارة.
                إذا لم يفتح واتساب تلقائياً، اضغط الزر بالأسفل.
              </p>
              <button type="button" class="btn-whatsapp" @click="openWhatsappForExisting">
                فتح واتساب الآن
              </button>
            </div>
          </section>

          <template v-if="form.house_status === 'under_construction'">
            <!-- 2. Systems needed -->
            <section class="bg-white rounded-2xl shadow p-6">
              <h3 class="section-title">2. الأنظمة المطلوبة</h3>
              <p class="text-sm text-gray-500 mb-3">ما هي الأنظمة التي تحتاج إليها في المنزل؟ (يمكن اختيار أكثر من خيار)</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label v-for="sys in systemOptions" :key="sys.value" class="checkbox-row">
                  <input type="checkbox" :value="sys.value" v-model="form.systems" />
                  {{ sys.label }}
                </label>
              </div>
              <div v-if="form.systems.includes('other')" class="mt-3">
                <input
                  v-model="form.systems_other"
                  type="text"
                  placeholder="اذكر النظام الآخر..."
                  class="sf-field"
                />
              </div>
            </section>

            <!-- 3. Required plans -->
            <section class="bg-white rounded-2xl shadow p-6">
              <h3 class="section-title">3. المخططات المطلوبة</h3>
              <p class="text-sm text-gray-500 mb-3">يرجى تحديد المخططات المتوفرة لديك (يفضّل إرفاقها):</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                <label v-for="plan in planOptions" :key="plan.value" class="checkbox-row">
                  <input type="checkbox" :value="plan.value" v-model="form.plans" />
                  {{ plan.label }}
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إرفاق ملفات المخططات (اختياري)</label>
                <input
                  type="file"
                  multiple
                  accept=".pdf,.jpg,.jpeg,.png,.webp"
                  class="w-full text-sm"
                  @change="handlePlanFiles"
                />
                <p v-if="planFiles.length" class="text-xs text-gray-500 mt-1">
                  {{ planFiles.length }} ملف تم اختياره
                </p>
              </div>
            </section>

            <!-- 4. Infrastructure -->
            <section class="bg-white rounded-2xl shadow p-6">
              <h3 class="section-title">4. أعمال البنية التحتية الخاصة بالأنظمة الذكية</h3>
              <p class="text-sm text-gray-500 mb-3">هل تحتاج إلى تنفيذ الأعمال المدنية الخاصة بالأنظمة الذكية؟</p>
              <div class="flex flex-col sm:flex-row gap-3">
                <label class="option-card" :class="{ 'option-card--active': form.infrastructure_by === 'contractor' }">
                  <input type="radio" value="contractor" v-model="form.infrastructure_by" class="sr-only" />
                  بواسطة مقاول المشروع
                </label>
                <label class="option-card" :class="{ 'option-card--active': form.infrastructure_by === 'company' }">
                  <input type="radio" value="company" v-model="form.infrastructure_by" class="sr-only" />
                  بواسطة شركتنا
                </label>
              </div>
            </section>

            <!-- 5. Proposed system -->
            <section class="bg-white rounded-2xl shadow p-6">
              <h3 class="section-title">5. النظام المقترح للمنزل</h3>
              <p class="text-sm text-gray-500 mb-3">ما هو النظام المناسب للمنزل؟</p>
              <div class="flex flex-col sm:flex-row gap-3">
                <label class="option-card" :class="{ 'option-card--active': form.proposed_system === 'wired' }">
                  <input type="radio" value="wired" v-model="form.proposed_system" class="sr-only" />
                  نظام كيبل (KNX / TIS)
                </label>
                <label class="option-card" :class="{ 'option-card--active': form.proposed_system === 'wireless' }">
                  <input type="radio" value="wireless" v-model="form.proposed_system" class="sr-only" />
                  نظام لاسلكي (Akubela ZIGBEE)
                </label>
              </div>
            </section>

            <!-- 6. Customer & site info -->
            <section class="bg-white rounded-2xl shadow p-6">
              <h3 class="section-title">6. بيانات الموقع والزيارة</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">اسم العميل *</label>
                  <input v-model="form.customer_name" type="text" required class="sf-field" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">رقم التواصل *</label>
                  <input v-model="form.customer_phone" type="tel" required class="sf-field" />
                </div>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">موقع المشروع</label>
                <input v-model="form.project_location" type="text" placeholder="المدينة / المنطقة" class="sf-field" />
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات إضافية</label>
                <textarea v-model="form.notes" rows="3" class="sf-field"></textarea>
              </div>
            </section>

            <button
              type="submit"
              :disabled="loading"
              class="w-full btn-primary justify-center py-3.5 text-base disabled:opacity-50"
            >
              {{ loading ? 'جاري الإرسال...' : 'إرسال الطلب | Submit' }}
            </button>
          </template>
        </form>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import api from '@/lib/api'

const systemOptions = [
  { value: 'cameras', label: 'نظام الكاميرات' },
  { value: 'internet', label: 'توزيع الإنترنت على كامل الفيلا' },
  { value: 'intercom', label: 'نظام الإنتركم' },
  { value: 'lighting', label: 'التحكم بالإضاءة' },
  { value: 'ac', label: 'التحكم بالمكيف' },
  { value: 'curtains', label: 'التحكم بالستائر' },
  { value: 'garage', label: 'ماكينة/بوابة الكراج' },
  { value: 'audio', label: 'نظام الصوت' },
  { value: 'other', label: 'أخرى' },
]

const planOptions = [
  { value: 'architectural', label: 'المخطط المعماري' },
  { value: 'electrical', label: 'مخطط الكهرباء' },
  { value: 'ac', label: 'مخطط التكييف' },
  { value: 'communications', label: 'مخطط الاتصالات' },
]

const form = ref({
  house_status: '' as 'under_construction' | 'existing' | '',
  systems: [] as string[],
  systems_other: '',
  plans: [] as string[],
  infrastructure_by: '' as 'contractor' | 'company' | '',
  proposed_system: '' as 'wired' | 'wireless' | '',
  customer_name: '',
  customer_phone: '',
  project_location: '',
  notes: '',
})

const planFiles = ref<File[]>([])
const loading = ref(false)
const error = ref('')
const submitted = ref(false)
const companyInfo = ref<{ contact: { whatsapp: string } } | null>(null)

const whatsappNumber = computed(() => companyInfo.value?.contact.whatsapp || '971562566232')

const openWhatsappForExisting = () => {
  const message = 'مرحباً، لدي منزل قائم بالفعل وأرغب بتحديد موعد لدراسة المشروع.'
  window.open(`https://wa.me/${whatsappNumber.value}?text=${encodeURIComponent(message)}`, '_blank')
}

const handleHouseStatusChange = () => {
  if (form.value.house_status === 'existing') {
    openWhatsappForExisting()
  }
}

const handlePlanFiles = (event: Event) => {
  const files = (event.target as HTMLInputElement).files
  planFiles.value = files ? Array.from(files) : []
}

const handleSubmit = async () => {
  error.value = ''

  if (form.value.house_status !== 'under_construction') {
    return
  }

  loading.value = true
  try {
    const formData = new FormData()
    formData.append('house_status', form.value.house_status)
    form.value.systems.forEach((s) => formData.append('systems[]', s))
    if (form.value.systems_other) formData.append('systems_other', form.value.systems_other)
    form.value.plans.forEach((p) => formData.append('plans[]', p))
    if (form.value.infrastructure_by) formData.append('infrastructure_by', form.value.infrastructure_by)
    if (form.value.proposed_system) formData.append('proposed_system', form.value.proposed_system)
    formData.append('customer_name', form.value.customer_name)
    formData.append('customer_phone', form.value.customer_phone)
    if (form.value.project_location) formData.append('project_location', form.value.project_location)
    if (form.value.notes) formData.append('notes', form.value.notes)
    planFiles.value.forEach((file) => formData.append('plan_files[]', file))

    await api.post('/study-requests', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    submitted.value = true
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (err: any) {
    error.value = err.response?.data?.message || 'حدث خطأ أثناء إرسال الطلب، يرجى المحاولة مرة أخرى'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const res = await fetch('/company-info.json')
    companyInfo.value = await res.json()
  } catch (err) {
    console.error('Error loading company info:', err)
  }
})
</script>

<style scoped>
.section-title {
  @apply text-lg font-bold text-[var(--sf-navy)] mb-3;
}

.option-card {
  @apply flex-1 text-center px-4 py-3 rounded-lg border-2 border-gray-200 text-gray-700 font-medium cursor-pointer transition-colors hover:border-blue-300;
}

.option-card--active {
  @apply border-blue-600 bg-blue-50 text-blue-800;
}

.checkbox-row {
  @apply flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 cursor-pointer hover:bg-gray-50;
}
</style>
