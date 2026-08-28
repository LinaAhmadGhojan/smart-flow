<template>
  <section class="py-20 bg-gradient-to-b from-slate-50 to-white" dir="rtl">
    <div class="container mx-auto px-4 max-w-6xl">

      <!-- العنوان -->
      <div class="text-center mb-14">
        <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-4 py-1.5 rounded-full mb-4 tracking-wide uppercase">
          Client Reviews
        </span>
        <h2 class="text-4xl font-extrabold text-slate-800 mb-3">آراء عملاء Smart Flow</h2>
        <p class="text-gray-500 text-base max-w-xl mx-auto">تجارب حقيقية من شركاء نجاحنا</p>

        <!-- متوسط النجوم -->
        <div v-if="stats.count > 0" class="mt-6 inline-flex items-center gap-3 bg-white border border-yellow-200 shadow-sm px-6 py-3 rounded-2xl">
          <div class="flex gap-0.5">
            <span v-for="s in 5" :key="s" class="text-xl" :class="s <= Math.round(stats.average) ? 'text-yellow-400' : 'text-gray-200'">★</span>
          </div>
          <span class="text-2xl font-bold text-slate-800">{{ stats.average }}</span>
          <span class="text-sm text-gray-400">/ 5 &nbsp;·&nbsp; {{ stats.count }} تقييم</span>
        </div>
      </div>

      <!-- loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
      </div>

      <!-- سلايدر الآراء -->
      <div
        v-else-if="reviews.length > 0"
        class="mb-16"
        aria-roledescription="carousel"
        aria-label="آراء العملاء"
      >
        <div
          class="reviews-slider__viewport max-w-2xl mx-auto min-h-[320px]"
          @mouseenter="pause"
          @mouseleave="resume"
        >
          <Transition :name="slideDir" mode="out-in">
            <article
              :key="currentReview.id"
              class="bg-white rounded-3xl shadow-md border border-slate-100 flex flex-col overflow-hidden"
            >
              <!-- media preview -->
              <div
                v-if="currentReview.reviewer_video || currentReview.reviewer_photo"
                class="relative w-full h-52 sm:h-56 overflow-hidden bg-slate-900 cursor-pointer flex-shrink-0"
                @click="currentReview.reviewer_video ? openVideo(currentReview.reviewer_video) : null"
              >
                <template v-if="currentReview.reviewer_video">
                  <video
                    :src="currentReview.reviewer_video"
                    class="w-full h-full object-cover opacity-80"
                    preload="metadata"
                    muted
                  ></video>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-14 h-14 bg-white/90 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
                      <svg class="w-6 h-6 text-blue-600 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                      </svg>
                    </div>
                  </div>
                  <span class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-0.5 rounded-full">📹 فيديو</span>
                </template>
                <img
                  v-else-if="currentReview.reviewer_photo"
                  :src="currentReview.reviewer_photo"
                  class="w-full h-full object-cover"
                  :alt="currentReview.display_name"
                />
              </div>

              <div class="p-6 sm:p-8 flex flex-col flex-1">
                <div class="flex gap-0.5 mb-4 justify-center">
                  <span
                    v-for="s in 5"
                    :key="s"
                    class="text-lg"
                    :class="s <= currentReview.rating ? 'text-yellow-400' : 'text-gray-200'"
                  >★</span>
                </div>

                <p class="text-slate-600 text-base sm:text-lg leading-relaxed text-center flex-1 mb-6">
                  <span class="text-blue-400 text-2xl font-serif leading-none">"</span>
                  {{ currentReview.comment }}
                  <span class="text-blue-400 text-2xl font-serif leading-none">"</span>
                </p>

                <div class="flex items-center gap-3 pt-5 border-t border-slate-100 justify-center">
                  <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 bg-blue-100 flex items-center justify-center ring-2 ring-blue-50">
                    <img
                      v-if="currentReview.reviewer_photo && !currentReview.reviewer_video"
                      :src="currentReview.reviewer_photo"
                      class="w-full h-full object-cover"
                      :alt="currentReview.display_name"
                    />
                    <span v-else class="text-blue-600 font-bold">{{ avatarLetter(currentReview.display_name) }}</span>
                  </div>
                  <div class="text-center sm:text-right">
                    <p class="font-semibold text-slate-800">{{ currentReview.display_name }}</p>
                    <p class="text-xs text-gray-400">{{ currentReview.created_at }}</p>
                  </div>
                  <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                </div>
              </div>
            </article>
          </Transition>
        </div>

        <!-- أسهم التنقل -->
        <div v-if="reviews.length > 1" class="flex items-center justify-center gap-4 mt-8">
          <button
            type="button"
            class="reviews-slider__arrow"
            aria-label="التقييم السابق"
            @click="prev"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
          </button>

          <span class="text-sm text-gray-400 tabular-nums min-w-[3.5rem] text-center">
            {{ currentIndex + 1 }} / {{ reviews.length }}
          </span>

          <button
            type="button"
            class="reviews-slider__arrow"
            aria-label="التقييم التالي"
            @click="next"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
        </div>
      </div>

      <p v-else class="text-center text-gray-400 mb-16 py-8">لا توجد تقييمات بعد. كن أول من يقيّم!</p>

      <!-- ======== مودال الفيديو ======== -->
      <Teleport to="body">
        <div
          v-if="videoModal"
          class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
          @click.self="videoModal = null"
        >
          <div class="relative w-full max-w-3xl">
            <button
              @click="videoModal = null"
              class="absolute -top-10 left-0 text-white/80 hover:text-white flex items-center gap-1 text-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              إغلاق
            </button>
            <video
              :src="videoModal"
              controls
              autoplay
              class="w-full rounded-2xl shadow-2xl bg-black"
              style="max-height:80vh"
            ></video>
          </div>
        </div>
      </Teleport>

      <!-- فورم الإرسال -->
      <div class="max-w-xl mx-auto bg-gradient-to-br from-blue-50 to-slate-50 rounded-3xl p-5 sm:p-8 border border-blue-100 shadow-sm">
        <div class="text-center mb-6">
          <h3 class="text-xl font-bold text-slate-800">شاركنا تجربتك</h3>
          <p class="text-sm text-gray-500 mt-1">رأيك يهمنا — Leave a Review</p>
        </div>

        <div v-if="submitted" class="text-center py-6">
          <svg class="w-16 h-16 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-green-700 font-semibold text-lg">شكراً على تقييمك! تم النشر.</p>
        </div>

        <form v-else @submit.prevent="submitReview" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              اسمك (اختياري) | Your name (optional)
            </label>
            <input
              v-model="form.reviewer_name"
              type="text"
              placeholder="أو اترك فارغاً للنشر مجهولاً"
              class="sf-field"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">التقييم | Rating *</label>
            <StarRating v-model:value="form.rating" />
            <p v-if="formErrors.rating" class="text-red-500 text-xs mt-1">{{ formErrors.rating }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تعليقك | Comment *</label>
            <textarea
              v-model="form.comment"
              rows="4"
              placeholder="شاركنا رأيك في خدماتنا..."
              class="sf-field sf-field--textarea"
            ></textarea>
            <p v-if="formErrors.comment" class="text-red-500 text-xs mt-1">{{ formErrors.comment }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">صورتك (اختياري) | Your photo</label>
            <input
              type="file"
              accept="image/jpeg,image/png,image/jpg,image/webp"
              @change="onPhotoChange"
              class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            <div v-if="photoPreview" class="mt-2">
              <img :src="photoPreview" class="w-16 h-16 rounded-full object-cover border border-blue-200" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">فيديو (اختياري) | Video — mp4, mov (max 50MB)</label>
            <input
              type="file"
              accept="video/mp4,video/quicktime,video/avi,video/webm"
              @change="onVideoChange"
              class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
            />
            <p v-if="videoName" class="text-xs text-purple-600 mt-1">📹 {{ videoName }}</p>
          </div>

          <p v-if="submitError" class="text-red-600 text-sm text-center">{{ submitError }}</p>

          <button
            type="submit"
            :disabled="submitting"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2"
          >
            <svg v-if="submitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            إرسال التقييم | Submit
          </button>
        </form>
      </div>

    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import StarRating from '@/components/StarRating.vue'

interface Review {
  id: number
  display_name: string
  reviewer_photo: string | null
  reviewer_video: string | null
  rating: number
  comment: string
  created_at: string
}

const INTERVAL_MS = 5000

const reviews = ref<Review[]>([])
const loading = ref(true)
const submitted = ref(false)
const submitting = ref(false)
const submitError = ref('')
const stats = reactive({ average: 0, count: 0 })
const videoModal = ref<string | null>(null)

const currentIndex = ref(0)
const paused = ref(false)
const slideDir = ref('hero-slide-next')

let timer: ReturnType<typeof setInterval> | null = null

const currentReview = computed(() => reviews.value[currentIndex.value])

function openVideo(url: string) {
  videoModal.value = url
}

const form = reactive({
  reviewer_name: '',
  rating: 0,
  comment: '',
})
const formErrors = reactive({ rating: '', comment: '' })

const photoFile = ref<File | null>(null)
const videoFile = ref<File | null>(null)
const photoPreview = ref<string | null>(null)
const videoName = ref('')

function onPhotoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  photoFile.value = file
  photoPreview.value = file ? URL.createObjectURL(file) : null
}

function onVideoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  videoFile.value = file
  videoName.value = file ? file.name : ''
}

const clearTimer = () => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

const startTimer = () => {
  clearTimer()
  if (reviews.value.length < 2 || paused.value) return
  timer = setInterval(() => {
    slideDir.value = 'hero-slide-next'
    currentIndex.value = (currentIndex.value + 1) % reviews.value.length
  }, INTERVAL_MS)
}

const pause = () => {
  paused.value = true
  clearTimer()
}

const resume = () => {
  paused.value = false
  startTimer()
}

const next = () => {
  if (reviews.value.length < 2) return
  slideDir.value = 'hero-slide-next'
  currentIndex.value = (currentIndex.value + 1) % reviews.value.length
  if (!paused.value) startTimer()
}

const prev = () => {
  if (reviews.value.length < 2) return
  slideDir.value = 'hero-slide-prev'
  currentIndex.value = (currentIndex.value - 1 + reviews.value.length) % reviews.value.length
  if (!paused.value) startTimer()
}

async function fetchReviews() {
  loading.value = true
  try {
    const res = await fetch('/api/reviews')
    const data = await res.json()
    reviews.value = data.reviews ?? []
    stats.average = data.average ?? 0
    stats.count = data.count ?? 0
    currentIndex.value = 0
    startTimer()
  } catch {
    // فشل التحميل
  } finally {
    loading.value = false
  }
}

async function submitReview() {
  formErrors.rating = ''
  formErrors.comment = ''
  submitError.value = ''

  let valid = true
  if (!form.rating) {
    formErrors.rating = 'الرجاء اختيار تقييم بالنجوم'
    valid = false
  }
  if (!form.comment.trim()) {
    formErrors.comment = 'الرجاء كتابة تعليق'
    valid = false
  }
  if (!valid) return

  submitting.value = true
  try {
    const fd = new FormData()
    if (form.reviewer_name) fd.append('reviewer_name', form.reviewer_name)
    fd.append('rating', String(form.rating))
    fd.append('comment', form.comment)
    if (photoFile.value) fd.append('reviewer_photo', photoFile.value)
    if (videoFile.value) fd.append('reviewer_video', videoFile.value)

    const res = await fetch('/api/reviews', {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: fd,
    })
    if (!res.ok) throw new Error('فشل إرسال التقييم')
    submitted.value = true
  } catch (e: any) {
    submitError.value = e.message ?? 'حدث خطأ. حاول مجدداً.'
  } finally {
    submitting.value = false
  }
}

function avatarLetter(name: string): string {
  return name?.[0]?.toUpperCase() ?? '?'
}

onMounted(fetchReviews)
onUnmounted(clearTimer)
</script>

<style scoped>
.reviews-slider__arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 9999px;
  border: 1.5px solid #cbd5e1;
  background: #fff;
  color: #1e40af;
  box-shadow: 0 2px 8px rgba(30, 64, 175, 0.08);
  transition: background 0.2s, border-color 0.2s, transform 0.15s;
}

.reviews-slider__arrow:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  transform: scale(1.05);
}

.reviews-slider__arrow:active {
  transform: scale(0.97);
}
</style>
