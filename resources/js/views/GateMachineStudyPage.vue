<template>
  <div class="min-h-screen sf-section">
    <Header />

    <div class="pt-28 pb-16">
      <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl mx-auto">
          <router-link to="/" class="inline-flex items-center gap-2 text-[var(--sf-accent)] hover:text-[var(--sf-navy)] mb-6 text-sm font-semibold transition-colors">
            <span aria-hidden="true">→</span>
            {{ t('backHome') }}</router-link>

          <div class="text-center">
            <p class="sf-eyebrow">{{ t('gateStudyEyebrow') }}</p>
            <h1 class="sf-heading">{{ t('gateStudyTitle') }}</h1>
            <p class="sf-subheading mt-3">
              {{ t('gateStudySubtitle') }}
            </p>
          </div>
        </div>

        <div v-if="submitted" class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8 text-center">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-[var(--sf-navy)] mb-2">{{ t('requestReceived') }}</h2>
          <p class="text-gray-600 mb-6">{{ t('gateRequestReceivedHint') }}</p>
          <router-link to="/" class="btn-primary">{{ t('backHome') }}</router-link>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="max-w-3xl mx-auto space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ error }}
          </div>

          <section class="bg-white rounded-2xl shadow p-6">
            <h3 class="section-title">{{ t('contactData') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('fullName') }}</label>
                <input v-model="form.customer_name" type="text" required class="sf-field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('phoneWhatsapp') }}</label>
                <input v-model="form.customer_phone" type="tel" required class="sf-field" />
              </div>
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('gateLocation') }}</label>
              <input v-model="form.site_location" type="text" required :placeholder="t('gateLocationPh')" class="sf-field" />
            </div>
          </section>

          <section class="bg-white rounded-2xl shadow p-6">
            <h3 class="section-title">{{ t('doorSpecs') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('doorWeight') }}</label>
                <input v-model="form.door_weight" type="text" :placeholder="t('example150')" class="sf-field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('doorWidth') }}</label>
                <input v-model="form.door_width" type="text" :placeholder="t('example400')" class="sf-field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('doorHeight') }}</label>
                <input v-model="form.door_height" type="text" :placeholder="t('example220')" class="sf-field" />
              </div>
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('doorMaterial') }}</label>
              <input v-model="form.door_material" type="text" :placeholder="t('doorMaterialPh')" class="sf-field" />
            </div>
          </section>

          <section class="bg-white rounded-2xl shadow p-6">
            <h3 class="section-title">{{ t('powerWiring') }}</h3>
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-500 mb-2">{{ t('hasElectricalPoint') }}</p>
                <div class="flex flex-wrap gap-3">
                  <label v-for="opt in yesNoOptions" :key="'elec-' + opt.value" class="option-card" :class="{ 'option-card--active': form.has_electrical_point === opt.value }">
                    <input type="radio" :value="opt.value" v-model="form.has_electrical_point" class="sr-only" />
                    {{ opt.label }}
                  </label>
                </div>
              </div>
              <div>
                <p class="text-sm text-gray-500 mb-2">{{ t('hasMachineWiring') }}</p>
                <div class="flex flex-wrap gap-3">
                  <label v-for="opt in yesNoOptions" :key="'wire-' + opt.value" class="option-card" :class="{ 'option-card--active': form.has_machine_wiring === opt.value }">
                    <input type="radio" :value="opt.value" v-model="form.has_machine_wiring" class="sr-only" />
                    {{ opt.label }}
                  </label>
                </div>
              </div>
            </div>
          </section>

          <section class="bg-white rounded-2xl shadow p-6">
            <h3 class="section-title">{{ t('extraNotes') }}</h3>
            <textarea v-model="form.notes" rows="3" class="sf-field" :placeholder="t('extraNotesPh')"></textarea>
          </section>

          <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" :disabled="loading" class="flex-1 btn-primary justify-center py-3.5 text-base disabled:opacity-50">
              {{ loading ? t('sending') : t('submitRequest') }}
            </button>
            <button type="button" class="flex-1 btn-whatsapp justify-center py-3.5 text-base" @click="openWhatsapp">
              {{ t('orWhatsapp') }}
            </button>
          </div>
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
import { useLocale } from '@/composables/useLocale'

const { t } = useLocale()

const yesNoOptions = computed(() => [
  { value: 'yes', label: t('yes') },
  { value: 'no', label: t('no') },
  { value: 'unknown', label: t('unknown') },
])

const form = ref({
  customer_name: '',
  customer_phone: '',
  site_location: '',
  door_weight: '',
  door_width: '',
  door_height: '',
  door_material: '',
  has_electrical_point: 'unknown' as 'yes' | 'no' | 'unknown',
  has_machine_wiring: 'unknown' as 'yes' | 'no' | 'unknown',
  notes: '',
})

const loading = ref(false)
const error = ref('')
const submitted = ref(false)
const companyInfo = ref<{ contact: { whatsapp: string } } | null>(null)

const whatsappNumber = computed(() => companyInfo.value?.contact.whatsapp || '971562566232')

const buildWhatsappMessage = () => {
  const f = form.value
  const lines = [
    'مرحباً، أرغب بطلب دراسة ماكينة باب في موقع خارجي.',
    f.customer_name ? `الاسم: ${f.customer_name}` : '',
    f.customer_phone ? `الهاتف: ${f.customer_phone}` : '',
    f.site_location ? `الموقع: ${f.site_location}` : '',
    f.door_weight ? `وزن الباب: ${f.door_weight} كغ` : '',
    f.door_width || f.door_height ? `أبعاد الباب: ${f.door_width || '—'} × ${f.door_height || '—'} سم` : '',
    f.door_material ? `نوع الباب: ${f.door_material}` : '',
  ].filter(Boolean)
  return lines.join('\n')
}

const openWhatsapp = () => {
  window.open(`https://wa.me/${whatsappNumber.value}?text=${encodeURIComponent(buildWhatsappMessage())}`, '_blank')
}

const handleSubmit = async () => {
  error.value = ''
  loading.value = true
  try {
    await api.post('/gate-machine-studies', form.value)
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
  @apply px-4 py-2.5 rounded-lg border-2 border-gray-200 text-gray-700 text-sm font-medium cursor-pointer transition-colors hover:border-blue-300;
}

.option-card--active {
  @apply border-blue-600 bg-blue-50 text-blue-800;
}
</style>
