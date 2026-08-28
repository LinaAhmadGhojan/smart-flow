<template>
  <div dir="rtl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">إدارة التقييمات | Reviews Management</h1>
    <div>

      <!-- إحصاء سريع -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5 text-center min-w-0">
          <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
          <p class="text-sm text-gray-500 mt-1">إجمالي التقييمات</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5 text-center min-w-0">
          <p class="text-3xl font-bold text-green-600">{{ stats.visible }}</p>
          <p class="text-sm text-gray-500 mt-1">ظاهر</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5 text-center min-w-0">
          <p class="text-3xl font-bold text-orange-500">{{ stats.pending }}</p>
          <p class="text-sm text-gray-500 mt-1">بانتظار الموافقة</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-5 text-center min-w-0">
          <div class="flex items-center justify-center gap-1">
            <span class="text-3xl font-bold text-yellow-500">{{ stats.average }}</span>
            <span class="text-yellow-400 text-2xl">★</span>
          </div>
          <p class="text-sm text-gray-500 mt-1">متوسط التقييم</p>
        </div>
      </div>

      <!-- زر إضافة + فلتر -->
      <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex gap-2">
          <button
            v-for="f in filters"
            :key="f.value"
            @click="activeFilter = f.value; fetchReviews()"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              activeFilter === f.value
                ? 'bg-blue-600 text-white'
                : 'bg-white text-gray-600 border hover:bg-gray-50',
            ]"
          >{{ f.label }}</button>
        </div>
        <button
          @click="showAddModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          إضافة تقييم | Add Review
        </button>
      </div>

      <!-- جدول التقييمات -->
      <div class="sf-card">
        <div v-if="loading" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="reviews.length === 0" class="p-12 text-center text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
          </svg>
          لا توجد تقييمات
        </div>

        <div v-else class="sf-table-wrap"><table class="sf-table">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-right font-medium text-gray-500">المراجع</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">التقييم</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">التعليق</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">المصدر</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">الحالة</th>
              <th class="px-4 py-3 text-center font-medium text-gray-500">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="review in reviews" :key="review.id" class="hover:bg-gray-50">
              <!-- المراجع -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 bg-blue-100 flex items-center justify-center">
                    <img v-if="review.reviewer_photo" :src="review.reviewer_photo" class="w-full h-full object-cover" :alt="review.display_name" />
                    <span v-else class="text-blue-600 font-bold text-xs">{{ avatarLetter(review.display_name) }}</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-800">{{ review.display_name }}</p>
                    <p class="text-xs text-gray-400">{{ review.created_at }}</p>
                    <span v-if="review.reviewer_video" class="text-xs text-purple-500">📹 فيديو</span>
                  </div>
                </div>
              </td>
              <!-- النجوم -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-0.5">
                  <span v-for="s in 5" :key="s" :class="s <= review.rating ? 'text-yellow-400' : 'text-gray-200'" class="text-lg">★</span>
                </div>
              </td>
              <!-- التعليق -->
              <td class="px-4 py-3 max-w-xs">
                <p class="text-gray-700 text-xs line-clamp-2">{{ review.comment }}</p>
              </td>
              <!-- المصدر -->
              <td class="px-4 py-3 text-center">
                <span :class="review.source === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700'" class="text-xs px-2 py-0.5 rounded-full">
                  {{ review.source === 'admin' ? 'أدمن' : 'عميل' }}
                </span>
              </td>
              <!-- الحالة -->
              <td class="px-4 py-3 text-center">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    class="sr-only peer"
                    :checked="review.is_visible"
                    @change="toggleVisibility(review)"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-checked:bg-green-500 rounded-full transition-colors
                              after:content-[''] after:absolute after:top-[2px] after:right-[2px]
                              after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                              peer-checked:after:translate-x-[-20px]"></div>
                  <span class="mr-2 text-xs" :class="review.is_visible ? 'text-green-600' : 'text-gray-400'">
                    {{ review.is_visible ? 'ظاهر' : 'مخفي' }}
                  </span>
                </label>
              </td>
              <!-- الإجراءات -->
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <!-- عرض الصورة/الفيديو -->
                  <button
                    v-if="review.reviewer_photo || review.reviewer_video"
                    @click="previewMedia(review)"
                    class="text-gray-500 hover:text-indigo-600 transition-colors"
                    title="عرض الصورة/الفيديو"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <!-- تعديل -->
                  <button
                    @click="openEdit(review)"
                    class="text-blue-500 hover:text-blue-700 transition-colors"
                    title="تعديل"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <!-- حذف -->
                  <button
                    @click="confirmDelete(review)"
                    class="text-red-500 hover:text-red-700 transition-colors"
                    title="حذف"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- ============ مودال إضافة تقييم ============ -->
    <Teleport to="body">
      <div v-if="showAddModal" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-4xl max-h-[95vh]">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900">إضافة تقييم | Add Review</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitNewReview" class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-4">
              <!-- الاسم -->
              <div>
                <label class="sf-label">الاسم (اختياري)</label>
                <input v-model="newReview.reviewer_name" type="text" placeholder="مجهول إذا تُرك فارغاً"
                  class="sf-field"/>
              </div>

              <!-- البريد -->
              <div>
                <label class="sf-label">البريد (اختياري)</label>
                <input v-model="newReview.reviewer_email" type="email" placeholder="example@email.com"
                  class="sf-field"/>
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <!-- صورة -->
              <div>
                <label class="sf-label">صورة المراجع (اختياري)</label>
                <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp" @change="onAdminPhotoChange"
                  class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                <div v-if="adminPhotoPreview" class="mt-2">
                  <img :src="adminPhotoPreview" class="w-20 h-20 rounded-full object-cover border border-blue-200" />
                </div>
              </div>

              <!-- فيديو -->
              <div>
                <label class="sf-label">فيديو (اختياري) — mp4, mov (max 50MB)</label>
                <input type="file" accept="video/mp4,video/quicktime,video/avi,video/webm" @change="onAdminVideoChange"
                  class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"/>
                <p v-if="adminVideoName" class="text-xs text-purple-600 mt-1">📹 {{ adminVideoName }}</p>
              </div>
            </div>

            <!-- النجوم -->
            <div>
              <label class="sf-label">التقييم *</label>
              <StarRating v-model:value="newReview.rating" />
              <p v-if="newErrors.rating" class="text-red-500 text-xs mt-1">{{ newErrors.rating }}</p>
            </div>

            <!-- التعليق -->
            <div>
              <label class="sf-label">التعليق *</label>
              <textarea v-model="newReview.comment" rows="12" placeholder="اكتب التقييم هنا..."
                class="sf-field sf-field--textarea min-h-[280px]"></textarea>
              <p v-if="newErrors.comment" class="text-red-500 text-xs mt-1">{{ newErrors.comment }}</p>
            </div>

            <p v-if="addError" class="text-red-600 text-sm">{{ addError }}</p>

            <div class="sf-actions border-t border-gray-200 pt-4">
              <button type="submit" :disabled="addLoading"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                <svg v-if="addLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                حفظ
              </button>
              <button type="button" @click="closeModal"
                class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                إلغاء
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ============ مودال تعديل تقييم ============ -->
    <Teleport to="body">
      <div v-if="editTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-4xl max-h-[95vh]">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900">تعديل التقييم</h3>
            <button @click="editTarget = null" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitEdit" class="space-y-5">
            <!-- الاسم -->
            <div>
              <label class="sf-label">الاسم (اختياري)</label>
              <input v-model="editForm.reviewer_name" type="text" placeholder="مجهول إذا تُرك فارغاً"
                class="sf-field"/>
            </div>

            <!-- التقييم -->
            <div>
              <label class="sf-label">التقييم *</label>
              <StarRating v-model:value="editForm.rating" />
              <p v-if="editErrors.rating" class="text-red-500 text-xs mt-1">{{ editErrors.rating }}</p>
            </div>

            <!-- التعليق -->
            <div>
              <label class="sf-label">التعليق *</label>
              <textarea v-model="editForm.comment" rows="12" placeholder="اكتب التقييم هنا..."
                class="sf-field sf-field--textarea min-h-[280px]"></textarea>
              <p v-if="editErrors.comment" class="text-red-500 text-xs mt-1">{{ editErrors.comment }}</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <!-- صورة جديدة -->
              <div>
                <label class="sf-label">تغيير الصورة (اختياري)</label>
                <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp" @change="onEditPhotoChange"
                  class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                <div v-if="editPhotoPreview" class="mt-2">
                  <img :src="editPhotoPreview" class="w-20 h-20 rounded-full object-cover border border-blue-200" />
                </div>
                <p v-else-if="editTarget?.reviewer_photo" class="text-xs text-gray-400 mt-1">صورة حالية موجودة</p>
              </div>

              <!-- فيديو جديد -->
              <div>
                <label class="sf-label">تغيير الفيديو (اختياري) — mp4, mov (max 50MB)</label>
                <input type="file" accept="video/mp4,video/quicktime,video/avi,video/webm" @change="onEditVideoChange"
                  class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"/>
                <p v-if="editVideoName" class="text-xs text-purple-600 mt-1">📹 {{ editVideoName }}</p>
                <p v-else-if="editTarget?.reviewer_video" class="text-xs text-gray-400 mt-1">فيديو حالي موجود</p>
              </div>
            </div>

            <p v-if="editError" class="text-red-600 text-sm">{{ editError }}</p>

            <div class="sf-actions border-t border-gray-200 pt-4">
              <button type="submit" :disabled="editLoading"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                <svg v-if="editLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                حفظ
              </button>
              <button type="button" @click="editTarget = null"
                class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                إلغاء
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ============ مودال عرض الصورة/الفيديو ============ -->
    <Teleport to="body">
      <div
        v-if="mediaPreview"
        class="fixed inset-0 z-50 bg-black/85 flex items-center justify-center p-4"
        @click.self="mediaPreview = null"
        dir="rtl"
      >
        <div class="relative w-full max-w-2xl">
          <button
            @click="mediaPreview = null"
            class="absolute -top-10 left-0 text-white/80 hover:text-white flex items-center gap-1 text-sm"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            إغلاق
          </button>

          <!-- اسم المراجع -->
          <p class="text-white/70 text-sm mb-3 text-center">{{ mediaPreview.name }}</p>

          <!-- فيديو -->
          <video
            v-if="mediaPreview.video"
            :src="mediaPreview.video"
            controls
            autoplay
            class="w-full rounded-2xl bg-black shadow-2xl"
            style="max-height:75vh"
          ></video>

          <!-- صورة -->
          <img
            v-else-if="mediaPreview.photo"
            :src="mediaPreview.photo"
            class="w-full rounded-2xl shadow-2xl object-contain"
            style="max-height:75vh"
          />

          <!-- كلاهما معاً -->
          <div v-if="mediaPreview.video && mediaPreview.photo" class="mt-3">
            <img
              :src="mediaPreview.photo"
              class="w-20 h-20 rounded-xl object-cover border-2 border-white/30 cursor-pointer hover:border-white transition-colors mx-auto"
              @click="mediaPreview = { ...mediaPreview, video: null }"
            />
            <p class="text-white/50 text-xs text-center mt-1">اضغط لعرض الصورة فقط</p>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ============ مودال تأكيد الحذف ============ -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="sf-modal-backdrop" dir="rtl">
        <div class="sf-modal-panel max-w-sm text-center">
          <svg class="w-14 h-14 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-bold text-gray-900 mb-2">حذف التقييم؟</p>
          <p class="text-gray-500 text-sm mb-6">لا يمكن التراجع عن هذا الإجراء.</p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null" class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
              إلغاء
            </button>
            <button @click="deleteReview" :disabled="deleteLoading"
              class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">
              حذف
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import StarRating from '@/components/StarRating.vue'

interface Review {
  id: number
  display_name: string
  reviewer_name: string | null
  reviewer_photo: string | null
  reviewer_video: string | null
  rating: number
  comment: string
  is_visible: boolean
  source: 'customer' | 'admin'
  created_at: string
}

// --- state ---
const reviews       = ref<Review[]>([])
const loading       = ref(true)
const showAddModal  = ref(false)
const deleteTarget  = ref<Review | null>(null)
const deleteLoading = ref(false)
const addLoading    = ref(false)
const addError      = ref('')

// --- edit state ---
const editTarget  = ref<Review | null>(null)
const editLoading = ref(false)
const editError   = ref('')
const editForm    = reactive({ reviewer_name: '', rating: 0, comment: '' })
const editErrors  = reactive({ rating: '', comment: '' })

const editPhotoFile    = ref<File | null>(null)
const editVideoFile    = ref<File | null>(null)
const editPhotoPreview = ref<string | null>(null)
const editVideoName    = ref('')

// --- media preview ---
const mediaPreview = ref<{ name: string; photo: string | null; video: string | null } | null>(null)

function previewMedia(review: Review) {
  mediaPreview.value = {
    name:  review.display_name,
    photo: review.reviewer_photo,
    video: review.reviewer_video,
  }
}

function onEditPhotoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  editPhotoFile.value    = file
  editPhotoPreview.value = file ? URL.createObjectURL(file) : null
}

function onEditVideoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  editVideoFile.value = file
  editVideoName.value = file ? file.name : ''
}

function openEdit(review: Review) {
  editTarget.value = review
  editForm.reviewer_name = review.reviewer_name ?? ''
  editForm.rating        = review.rating
  editForm.comment       = review.comment
  editPhotoFile.value    = null
  editVideoFile.value    = null
  editPhotoPreview.value = null
  editVideoName.value    = ''
  editError.value  = ''
  editErrors.rating  = ''
  editErrors.comment = ''
}

async function submitEdit() {
  editErrors.rating  = ''
  editErrors.comment = ''
  editError.value    = ''

  let valid = true
  if (!editForm.rating)          { editErrors.rating  = 'اختر التقييم'; valid = false }
  if (!editForm.comment.trim())  { editErrors.comment = 'اكتب التعليق'; valid = false }
  if (!valid || !editTarget.value) return

  editLoading.value = true
  try {
    const fd = new FormData()
    fd.append('_method', 'PUT')
    if (editForm.reviewer_name) fd.append('reviewer_name', editForm.reviewer_name)
    fd.append('rating',  String(editForm.rating))
    fd.append('comment', editForm.comment)
    if (editPhotoFile.value) fd.append('reviewer_photo', editPhotoFile.value)
    if (editVideoFile.value) fd.append('reviewer_video', editVideoFile.value)

    const res = await fetch(`/api/admin/reviews/${editTarget.value.id}`, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: fd,
    })
    if (!res.ok) throw new Error('فشل التعديل')
    const updated = await res.json()
    const idx = reviews.value.findIndex(r => r.id === editTarget.value!.id)
    if (idx !== -1) Object.assign(reviews.value[idx], updated)
    editTarget.value = null
  } catch (e: any) {
    editError.value = e.message ?? 'حدث خطأ'
  } finally {
    editLoading.value = false
  }
}

const activeFilter = ref('all')
const filters = [
  { label: 'الكل', value: 'all' },
  { label: 'ظاهر', value: 'visible' },
  { label: 'مخفي', value: 'hidden' },
]

const stats = reactive({ total: 0, visible: 0, pending: 0, average: 0 })

const newReview = reactive({
  reviewer_name: '',
  reviewer_email: '',
  rating: 0,
  comment: '',
})
const newErrors = reactive({ rating: '', comment: '' })

const adminPhotoFile    = ref<File | null>(null)
const adminVideoFile    = ref<File | null>(null)
const adminPhotoPreview = ref<string | null>(null)
const adminVideoName    = ref('')

function onAdminPhotoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  adminPhotoFile.value    = file
  adminPhotoPreview.value = file ? URL.createObjectURL(file) : null
}

function onAdminVideoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  adminVideoFile.value = file
  adminVideoName.value = file ? file.name : ''
}

// --- fetch ---
async function fetchReviews() {
  loading.value = true
  try {
    let url = '/api/admin/reviews'
    if (activeFilter.value === 'visible') url += '?visible=true'
    if (activeFilter.value === 'hidden')  url += '?visible=false'

    const res  = await fetch(url)
    const data = await res.json()
    reviews.value    = data.reviews ?? []
    stats.total      = data.total ?? 0
    stats.visible    = data.visible_count ?? 0
    stats.pending    = data.pending_count ?? 0
    stats.average    = data.average ?? 0
  } finally {
    loading.value = false
  }
}

// --- toggle visibility ---
async function toggleVisibility(review: Review) {
  const newVal = !review.is_visible
  // تحديث فوري في UI بدون انتظار السيرفر
  review.is_visible = newVal
  stats.visible = reviews.value.filter(r => r.is_visible).length
  stats.pending = reviews.value.filter(r => !r.is_visible).length

  await fetch(`/api/admin/reviews/${review.id}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ is_visible: newVal }),
  })
}

// --- add review ---
function closeModal() {
  showAddModal.value = false
  Object.assign(newReview, { reviewer_name: '', reviewer_email: '', rating: 0, comment: '' })
  adminPhotoFile.value    = null
  adminVideoFile.value    = null
  adminPhotoPreview.value = null
  adminVideoName.value    = ''
  addError.value   = ''
  newErrors.rating  = ''
  newErrors.comment = ''
}

async function submitNewReview() {
  newErrors.rating  = ''
  newErrors.comment = ''
  addError.value    = ''

  let valid = true
  if (!newReview.rating) { newErrors.rating = 'اختر التقييم'; valid = false }
  if (!newReview.comment.trim()) { newErrors.comment = 'اكتب التعليق'; valid = false }
  if (!valid) return

  addLoading.value = true
  try {
    const fd = new FormData()
    if (newReview.reviewer_name)    fd.append('reviewer_name',    newReview.reviewer_name)
    if (newReview.reviewer_email)   fd.append('reviewer_email',   newReview.reviewer_email)
    fd.append('rating',     String(newReview.rating))
    fd.append('comment',    newReview.comment)
    fd.append('is_visible', '1')
    if (adminPhotoFile.value) fd.append('reviewer_photo', adminPhotoFile.value)
    if (adminVideoFile.value) fd.append('reviewer_video', adminVideoFile.value)

    const res = await fetch('/api/admin/reviews', {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: fd,
    })
    if (!res.ok) throw new Error('فشل الحفظ')
    closeModal()
    await fetchReviews()
  } catch (e: any) {
    addError.value = e.message ?? 'حدث خطأ'
  } finally {
    addLoading.value = false
  }
}

// --- delete ---
function confirmDelete(review: Review) {
  deleteTarget.value = review
}

async function deleteReview() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await fetch(`/api/admin/reviews/${deleteTarget.value.id}`, { method: 'DELETE' })
    reviews.value = reviews.value.filter(r => r.id !== deleteTarget.value!.id)
    stats.total   = reviews.value.length
    stats.visible = reviews.value.filter(r => r.is_visible).length
    stats.pending = reviews.value.filter(r => !r.is_visible).length
    deleteTarget.value = null
  } finally {
    deleteLoading.value = false
  }
}

// --- helper ---
function avatarLetter(name: string): string {
  return name?.[0]?.toUpperCase() ?? '?'
}

onMounted(fetchReviews)
</script>
