<template>
  <div>
    <div>
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">إعدادات الشركة | Company Settings</h1>
        <p class="text-gray-600 mt-2">تعديل معلومات التواصل وبيانات الشركة</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ successMessage }}</span>
        <button @click="successMessage = ''" class="absolute top-0 bottom-0 left-0 px-4">
          <span class="text-2xl">&times;</span>
        </button>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ errorMessage }}</span>
        <button @click="errorMessage = ''" class="absolute top-0 bottom-0 left-0 px-4">
          <span class="text-2xl">&times;</span>
        </button>
      </div>

      <!-- Settings Form -->
      <div class="sf-card p-4 sm:p-6 md:p-8 w-full">
        <form @submit.prevent="saveSettings">
          <!-- Company Info Section -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-3 border-b">
              معلومات الشركة | Company Information
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Company Name English -->
              <div>
                <label class="sf-label">
                  اسم الشركة (English)
                </label>
                <input
                  v-model="settings.companyName"
                  type="text"
                  class="sf-field"
                  required
                />
              </div>

              <!-- Company Name Arabic -->
              <div>
                <label class="sf-label">
                  اسم الشركة (عربي)
                </label>
                <input
                  v-model="settings.companyNameAr"
                  type="text"
                  dir="rtl"
                  class="sf-field"
                  required
                />
              </div>

              <!-- TRN -->
              <div>
                <label class="sf-label">
                  الرقم الضريبي | TRN
                </label>
                <input
                  v-model="settings.trn"
                  type="text"
                  class="sf-field"
                  placeholder="مثال: 105016234400001"
                />
              </div>

              <!-- Tagline English -->
              <div>
                <label class="sf-label">
                  الشعار (English)
                </label>
                <input
                  v-model="settings.tagline"
                  type="text"
                  class="sf-field"
                  required
                />
              </div>

              <!-- Tagline Arabic -->
              <div>
                <label class="sf-label">
                  الشعار (عربي)
                </label>
                <input
                  v-model="settings.taglineAr"
                  type="text"
                  dir="rtl"
                  class="sf-field"
                  required
                />
              </div>

              <!-- Description English -->
              <div>
                <label class="sf-label">
                  الوصف (English)
                </label>
                <textarea
                  v-model="settings.description"
                  rows="3"
                  class="sf-field"
                  required
                ></textarea>
              </div>

              <!-- Description Arabic -->
              <div>
                <label class="sf-label">
                  الوصف (عربي)
                </label>
                <textarea
                  v-model="settings.descriptionAr"
                  rows="3"
                  dir="rtl"
                  class="sf-field"
                  required
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Contact Info Section -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-3 border-b">
              معلومات التواصل | Contact Information
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Email -->
              <div>
                <label class="sf-label">
                  البريد الإلكتروني | Email
                </label>
                <input
                  v-model="settings.contact.email"
                  type="email"
                  class="sf-field"
                  required
                />
              </div>

              <!-- Phone -->
              <div>
                <label class="sf-label">
                  رقم الهاتف | Phone
                </label>
                <input
                  v-model="settings.contact.phone"
                  type="text"
                  class="sf-field"
                  placeholder="+971 50 123 4567"
                  required
                />
              </div>

              <!-- WhatsApp -->
              <div>
                <label class="sf-label">
                  واتساب | WhatsApp
                  <span class="text-sm text-gray-500">(بدون +)</span>
                </label>
                <input
                  v-model="settings.contact.whatsapp"
                  type="text"
                  class="sf-field"
                  placeholder="971501234567"
                  required
                />
              </div>

              <!-- Address English -->
              <div>
                <label class="sf-label">
                  العنوان (English)
                </label>
                <input
                  v-model="settings.contact.address.en"
                  type="text"
                  class="sf-field"
                  required
                />
              </div>

              <!-- Address Arabic -->
              <div class="md:col-span-2">
                <label class="sf-label">
                  العنوان (عربي)
                </label>
                <input
                  v-model="settings.contact.address.ar"
                  type="text"
                  dir="rtl"
                  class="sf-field"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Working Hours Section -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-3 border-b">
              ساعات العمل | Working Hours
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Working Hours English -->
              <div>
                <label class="sf-label">
                  ساعات العمل (English)
                </label>
                <input
                  v-model="settings.workingHours.en"
                  type="text"
                  class="sf-field"
                  placeholder="Sunday - Thursday: 9:00 AM - 6:00 PM"
                  required
                />
              </div>

              <!-- Working Hours Arabic -->
              <div>
                <label class="sf-label">
                  ساعات العمل (عربي)
                </label>
                <input
                  v-model="settings.workingHours.ar"
                  type="text"
                  dir="rtl"
                  class="sf-field"
                  placeholder="الأحد - الخميس: 9:00 صباحاً - 6:00 مساءً"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Social Media Section -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-3 border-b">
              وسائل التواصل الاجتماعي | Social Media
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Facebook -->
              <div>
                <label class="sf-label">
                  Facebook
                </label>
                <input
                  v-model="settings.social.facebook"
                  type="url"
                  class="sf-field"
                  placeholder="https://facebook.com/yourpage"
                />
              </div>

              <!-- Instagram -->
              <div>
                <label class="sf-label">
                  Instagram
                </label>
                <input
                  v-model="settings.social.instagram"
                  type="url"
                  class="sf-field"
                  placeholder="https://instagram.com/yourpage"
                />
              </div>

              <!-- Twitter -->
              <div>
                <label class="sf-label">
                  Twitter
                </label>
                <input
                  v-model="settings.social.twitter"
                  type="url"
                  class="sf-field"
                  placeholder="https://twitter.com/yourpage"
                />
              </div>

              <!-- LinkedIn -->
              <div>
                <label class="sf-label">
                  LinkedIn
                </label>
                <input
                  v-model="settings.social.linkedin"
                  type="url"
                  class="sf-field"
                  placeholder="https://linkedin.com/company/yourpage"
                />
              </div>
            </div>
          </div>

          <!-- About Section -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-3 border-b">
              عن الشركة | About Us
            </h2>
            
            <div class="grid md:grid-cols-1 gap-6">
              <!-- About English -->
              <div>
                <label class="sf-label">
                  نبذة عن الشركة (English)
                </label>
                <textarea
                  v-model="settings.about.en"
                  rows="4"
                  class="sf-field"
                  required
                ></textarea>
              </div>

              <!-- About Arabic -->
              <div>
                <label class="sf-label">
                  نبذة عن الشركة (عربي)
                </label>
                <textarea
                  v-model="settings.about.ar"
                  rows="4"
                  dir="rtl"
                  class="sf-field"
                  required
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex items-center justify-end gap-4 pt-6 border-t">
            <router-link
              to="/admin/dashboard"
              class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              إلغاء
            </router-link>
            <button
              type="submit"
              :disabled="saving"
              class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'جاري الحفظ...' : 'حفظ التعديلات | Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Logo & Signature Section (used on exported PDF reports) -->
      <div class="bg-white rounded-xl shadow-lg p-8 mt-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2 pb-3 border-b">
          الشعار والتوقيع | Logo & Signature
        </h2>
          <p class="text-gray-500 text-sm mb-6">تظهر هذه العناصر تلقائياً في عروض الأسعار والفواتير وتقارير الزيارات المُصدّرة PDF.</p>

        <div v-if="brandingSuccess" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
          {{ brandingSuccess }}
        </div>
        <div v-if="brandingError" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
          {{ brandingError }}
        </div>

        <div class="grid md:grid-cols-2 gap-8">
          <!-- Logo -->
          <div>
            <label class="block text-gray-700 font-medium mb-2">شعار الشركة</label>
            <div class="flex items-center gap-4 mb-3">
              <div class="w-20 h-20 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50">
                <img v-if="logoPreview || branding.logo" :src="(logoPreview || branding.logo) as string" class="w-full h-full object-contain" @error="handleMediaError" />
                <span v-else class="text-gray-300 text-xs">لا يوجد</span>
              </div>
              <input type="file" accept="image/png,image/jpeg,image/jpg,image/webp" @change="onLogoChange"
                class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
            </div>
          </div>

          <!-- Signature -->
          <div>
            <label class="block text-gray-700 font-medium mb-2">توقيع/ختم الشركة</label>
            <div class="flex items-center gap-4 mb-3">
              <div class="w-20 h-20 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50">
                <img v-if="signaturePreview || branding.signature" :src="(signaturePreview || branding.signature) as string" class="w-full h-full object-contain" @error="handleMediaError" />
                <span v-else class="text-gray-300 text-xs">لا يوجد</span>
              </div>
              <input type="file" accept="image/png,image/jpeg,image/jpg,image/webp" @change="onSignatureChange"
                class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <label class="sf-label">اسم/مسمى الموقّع (اختياري)</label>
          <input v-model="signatureName" type="text" placeholder="مثال: المدير العام - SmartFlow"
            class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
        </div>

        <div class="flex justify-end mt-6">
          <button
            type="button"
            :disabled="brandingSaving"
            @click="saveBranding"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors disabled:opacity-50"
          >
            {{ brandingSaving ? 'جاري الحفظ...' : 'حفظ الشعار والتوقيع' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/api'
import { mediaUrl, handleMediaError } from '@/lib/media'

const router = useRouter()

interface Settings {
  companyName: string
  companyNameAr: string
  trn: string
  tagline: string
  taglineAr: string
  description: string
  descriptionAr: string
  footerDescAr: string
  contact: {
    email: string
    phone: string
    whatsapp: string
    address: {
      en: string
      ar: string
    }
  }
  workingHours: {
    en: string
    ar: string
  }
  social: {
    facebook: string
    twitter: string
    instagram: string
    linkedin: string
  }
  about: {
    en: string
    ar: string
  }
  seo: {
    keywords: string
    location: {
      city: string
      cityAr: string
      country: string
      countryAr: string
      countryCode: string
      region: string
    }
  }
}

const settings = ref<Settings>({
  companyName: '',
  companyNameAr: '',
  trn: '',
  tagline: '',
  taglineAr: '',
  description: '',
  descriptionAr: '',
  footerDescAr: '',
  contact: {
    email: '',
    phone: '',
    whatsapp: '',
    address: {
      en: '',
      ar: ''
    }
  },
  workingHours: {
    en: '',
    ar: ''
  },
  social: {
    facebook: '',
    twitter: '',
    instagram: '',
    linkedin: ''
  },
  about: {
    en: '',
    ar: ''
  },
  seo: {
    keywords: '',
    location: {
      city: 'Dubai',
      cityAr: 'دبي',
      country: 'United Arab Emirates',
      countryAr: 'الإمارات العربية المتحدة',
      countryCode: 'AE',
      region: 'Middle East'
    }
  }
})

const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// --- Logo & signature branding ---
const branding = ref<{ logo: string | null; signature: string | null }>({ logo: null, signature: null })
const signatureName = ref('')
const logoFile = ref<File | null>(null)
const signatureFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)
const signaturePreview = ref<string | null>(null)
const brandingSaving = ref(false)
const brandingSuccess = ref('')
const brandingError = ref('')

const loadSettings = async () => {
  try {
    const response = await api.get('/settings')
    const data = response.data
    
    // دمج البيانات مع القيم الافتراضية للتأكد من وجود جميع الحقول
    settings.value = {
      companyName: data.companyName || '',
      companyNameAr: data.companyNameAr || '',
      trn: data.trn || data.taxNumber || '',
      tagline: data.tagline || '',
      taglineAr: data.taglineAr || '',
      description: data.description || '',
      descriptionAr: data.descriptionAr || '',
      footerDescAr: data.footerDescAr || '',
      contact: {
        email: data.contact?.email || '',
        phone: data.contact?.phone || '',
        whatsapp: data.contact?.whatsapp || '',
        address: {
          en: data.contact?.address?.en || '',
          ar: data.contact?.address?.ar || ''
        }
      },
      workingHours: {
        en: data.workingHours?.en || '',
        ar: data.workingHours?.ar || ''
      },
      social: {
        facebook: data.social?.facebook || '',
        twitter: data.social?.twitter || '',
        instagram: data.social?.instagram || '',
        linkedin: data.social?.linkedin || ''
      },
      about: {
        en: data.about?.en || '',
        ar: data.about?.ar || ''
      },
      seo: {
        keywords: data.seo?.keywords || '',
        location: {
          city: data.seo?.location?.city || 'Dubai',
          cityAr: data.seo?.location?.cityAr || 'دبي',
          country: data.seo?.location?.country || 'United Arab Emirates',
          countryAr: data.seo?.location?.countryAr || 'الإمارات العربية المتحدة',
          countryCode: data.seo?.location?.countryCode || 'AE',
          region: data.seo?.location?.region || 'Middle East'
        }
      }
    }
    branding.value.logo = data.logo ? mediaUrl(data.logo) : null
    branding.value.signature = data.signature ? mediaUrl(data.signature) : null
    signatureName.value = data.signatureName || ''
  } catch (error) {
    console.error('Error loading settings:', error)
    errorMessage.value = 'فشل تحميل الإعدادات - استخدام القيم الافتراضية'
  }
}

const onLogoChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  logoFile.value = file
  logoPreview.value = file ? URL.createObjectURL(file) : null
}

const onSignatureChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  signatureFile.value = file
  signaturePreview.value = file ? URL.createObjectURL(file) : null
}

const saveBranding = async () => {
  brandingSaving.value = true
  brandingSuccess.value = ''
  brandingError.value = ''
  try {
    const fd = new FormData()
    if (logoFile.value) fd.append('logo', logoFile.value)
    if (signatureFile.value) fd.append('signature', signatureFile.value)
    if (signatureName.value) fd.append('signature_name', signatureName.value)

    const res = await api.post('/settings/branding', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    if (res.data.logo) branding.value.logo = mediaUrl(res.data.logo)
    if (res.data.signature) branding.value.signature = mediaUrl(res.data.signature)
    logoFile.value = null
    signatureFile.value = null
    logoPreview.value = null
    signaturePreview.value = null
    brandingSuccess.value = '✅ تم حفظ الشعار والتوقيع بنجاح!'
    await loadSettings()
  } catch (err: any) {
    brandingError.value = err.response?.data?.error || 'حدث خطأ أثناء حفظ الشعار/التوقيع'
  } finally {
    brandingSaving.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/settings', settings.value)
    successMessage.value = '✅ تم حفظ الإعدادات بنجاح!'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    await loadSettings()
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error || error.message || 'فشل في حفظ الإعدادات'
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
input:focus,
textarea:focus {
  outline: none;
}
</style>
