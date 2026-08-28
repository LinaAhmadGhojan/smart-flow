<template>
  <div>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-900">إدارة المواعيد | Appointments</h1>
      <button
        type="button"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        @click="openDayModal(todayStr); startAdd()"
      >
        + موعد جديد
      </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6">
      <!-- Month navigation -->
      <div class="flex items-center justify-between mb-4">
        <button type="button" class="text-blue-700 hover:text-blue-900 text-sm font-semibold px-2 py-1" @click="changeMonth(-1)">‹ السابق</button>
        <h2 class="text-lg font-bold text-gray-900">{{ monthLabel }}</h2>
        <button type="button" class="text-blue-700 hover:text-blue-900 text-sm font-semibold px-2 py-1" @click="changeMonth(1)">التالي ›</button>
      </div>

      <!-- Weekday headers -->
      <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-gray-400 mb-1">
        <div v-for="d in weekdayLabels" :key="d">{{ d }}</div>
      </div>

      <div v-if="loading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
      </div>

      <!-- Calendar grid -->
      <div v-else class="grid grid-cols-7 gap-y-1 max-w-sm mx-auto">
        <div v-for="day in calendarDays" :key="day.date" class="flex items-center justify-center py-0.5">
          <button
            type="button"
            class="relative w-8 h-8 md:w-9 md:h-9 rounded-full flex items-center justify-center text-xs md:text-sm transition-colors"
            :class="[
              day.inCurrentMonth ? 'text-gray-700' : 'text-gray-300',
              day.isToday ? 'bg-[var(--sf-accent)] text-white font-bold' : 'hover:bg-gray-100',
            ]"
            @click="openDayModal(day.date)"
          >
            {{ day.dayNum }}
            <span
              v-if="day.appointments.length"
              class="absolute -bottom-0.5 w-1.5 h-1.5 rounded-full"
              :class="day.isToday ? 'bg-white' : 'bg-green-500'"
            ></span>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-center gap-4 mt-4 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span> يوجد مواعيد</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[var(--sf-accent)]"></span> اليوم</span>
      </div>
    </div>

    <!-- Day details / add / edit modal -->
    <Teleport to="body">
      <div
        v-if="selectedDate"
        class="sf-modal-backdrop"
        dir="rtl"
        @click.self="closeDayModal"
      >
        <div class="sf-modal-panel max-w-4xl max-h-[95vh]">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900">{{ formatFullDate(selectedDate) }}</h3>
            <button type="button" @click="closeDayModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Existing appointments -->
          <div v-if="dayAppointments.length" class="space-y-4 mb-6">
            <div v-for="appt in dayAppointments" :key="appt.id" class="border border-gray-100 rounded-xl p-4 flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <span
                    class="text-xs font-bold px-2 py-0.5 rounded-full"
                    :class="appt.type === 'maintenance' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'"
                  >
                    {{ appt.type === 'maintenance' ? 'صيانة' : 'زيارة' }}
                  </span>
                  <span class="font-semibold text-gray-800 text-sm">
                    {{ formatTime(appt.start_time) }}<span v-if="appt.end_time"> - {{ formatTime(appt.end_time) }}</span>
                  </span>
                </div>
                <p class="text-sm text-gray-700">
                  <strong>العميل:</strong> {{ appt.customer_name || '—' }}
                  <span v-if="appt.customer_phone" class="text-gray-500"> · {{ appt.customer_phone }}</span>
                </p>
                <p class="text-sm text-gray-700">
                  <strong>المهندس:</strong> {{ appt.engineer_name || '—' }}
                  <span v-if="appt.engineer_phone" class="text-gray-500"> · {{ appt.engineer_phone }}</span>
                </p>
                <p v-if="appt.location" class="text-sm text-gray-500 mt-0.5">📍 {{ appt.location }}</p>
                <p v-if="appt.notes" class="text-sm text-gray-500 mt-0.5">{{ appt.notes }}</p>
                <p v-if="appt.study_request" class="text-xs text-blue-600 mt-1">مرتبط بطلب دراسة مشروع</p>

                <div class="flex items-center gap-3 mt-2">
                  <a
                    v-if="whatsappLink(appt.customer_phone)"
                    :href="whatsappLink(appt.customer_phone, appointmentMessage(appt))!"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-1 text-xs text-green-600 hover:underline"
                    title="واتساب العميل"
                  >
                    <svg class="w-4 h-4" viewBox="0 0 32 32" fill="currentColor"><path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/></svg>
                    واتساب العميل
                  </a>
                  <a
                    v-if="mailtoLink(appt.customer_email)"
                    :href="mailtoLink(appt.customer_email, 'تأكيد موعد SmartFlow', appointmentMessage(appt))!"
                    class="flex items-center gap-1 text-xs text-blue-500 hover:underline"
                    title="إيميل العميل"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    إيميل
                  </a>
                  <a
                    v-if="whatsappLink(appt.engineer_phone)"
                    :href="whatsappLink(appt.engineer_phone, appointmentMessage(appt))!"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-1 text-xs text-teal-600 hover:underline"
                    title="واتساب المهندس"
                  >
                    <svg class="w-4 h-4" viewBox="0 0 32 32" fill="currentColor"><path d="M16.03 5.3c-5.9 0-10.68 4.72-10.68 10.55 0 1.86.49 3.67 1.42 5.28L5.3 26.7l5.74-1.5a10.77 10.77 0 0 0 4.98 1.22h.01c5.89 0 10.67-4.73 10.67-10.56 0-2.82-1.1-5.46-3.12-7.45a10.75 10.75 0 0 0-7.55-3.1zm0 19.33h-.01a8.9 8.9 0 0 1-4.53-1.24l-.33-.19-3.4.89.91-3.31-.21-.34a8.73 8.73 0 0 1-1.35-4.6c0-4.83 3.98-8.77 8.91-8.77 2.38 0 4.61.92 6.29 2.58a8.67 8.67 0 0 1 2.61 6.18c0 4.83-3.99 8.8-8.89 8.8zm4.88-6.61c-.27-.13-1.61-.79-1.86-.88-.25-.09-.43-.13-.61.13-.18.26-.7.88-.86 1.06-.16.18-.31.2-.58.07-.27-.13-1.13-.41-2.16-1.31-.8-.7-1.34-1.56-1.5-1.82-.16-.26-.02-.4.12-.53.12-.12.27-.31.4-.46.13-.15.18-.26.27-.44.09-.18.04-.33-.02-.46-.07-.13-.61-1.46-.84-2-.22-.53-.44-.46-.61-.47h-.52c-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2 0 1.3.95 2.56 1.09 2.74.13.18 1.86 2.97 4.52 4.16.63.27 1.12.43 1.51.55.63.2 1.2.17 1.65.1.5-.08 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.52-.31z"/></svg>
                    واتساب المهندس
                  </a>
                </div>
              </div>
              <div class="flex flex-col gap-2 shrink-0 text-xs">
                <button type="button" class="text-blue-600 hover:underline" @click="startEdit(appt)">تعديل</button>
                <button type="button" class="text-red-600 hover:underline" @click="deleteAppt(appt)">حذف</button>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-400 text-sm mb-4">لا توجد مواعيد في هذا اليوم</p>

          <!-- Add / edit form -->
          <div v-if="isPastSelectedDate && !showForm" class="text-sm text-gray-400 bg-gray-50 rounded-lg p-3 text-center">
            لا يمكن إضافة مواعيد جديدة في تاريخ سابق.
          </div>
          <template v-else>
            <button
              v-if="!showForm"
              type="button"
              class="w-full border-2 border-dashed border-gray-300 hover:border-blue-400 text-gray-500 hover:text-blue-600 rounded-xl py-2.5 text-sm font-medium transition-colors"
              @click="startAdd"
            >
              + إضافة موعد لهذا اليوم
            </button>

            <form v-else @submit.prevent="submitForm" class="space-y-5 border-t border-gray-100 pt-5 mt-5">
              <div>
                <label class="sf-label">التاريخ</label>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                  <button
                    type="button"
                    class="quick-date-btn"
                    :class="{ 'quick-date-btn--active': form.date === todayStr }"
                    @click="setFormDate(0)"
                  >
                    اليوم
                  </button>
                  <button
                    type="button"
                    class="quick-date-btn"
                    :class="{ 'quick-date-btn--active': form.date === addDaysStr(1) }"
                    @click="setFormDate(1)"
                  >
                    بكرا
                  </button>
                  <button
                    type="button"
                    class="quick-date-btn"
                    :class="{ 'quick-date-btn--active': form.date === addDaysStr(7) }"
                    @click="setFormDate(7)"
                  >
                    الأسبوع الجاي
                  </button>
                </div>
                <input v-model="form.date" type="date" :min="todayStr" required class="sf-field" />
              </div>

              <div class="sf-form-grid">
                <div>
                  <label class="sf-label">من</label>
                  <input v-model="form.start_time" type="time" min="09:00" max="19:00" required class="sf-field" />
                </div>
                <div>
                  <label class="sf-label">إلى</label>
                  <input v-model="form.end_time" type="time" min="09:00" max="19:00" required class="sf-field" />
                </div>
              </div>

              <div>
                <label class="sf-label">نوع الموعد</label>
                <select v-model="form.type" class="sf-field">
                  <option value="visit">زيارة</option>
                  <option value="maintenance">صيانة</option>
                </select>
              </div>

              <div class="sf-form-grid">
                <div>
                  <label class="sf-label">اسم العميل</label>
                  <input
                    v-model="form.customer_name"
                    @change="onCustomerNameChange"
                    list="customer-suggestions"
                    type="text"
                    class="sf-field"
                    placeholder="اسم العميل"
                  />
                </div>
                <div>
                  <label class="sf-label">هاتف العميل</label>
                  <input v-model="form.customer_phone" type="text" class="sf-field" placeholder="05xxxxxxxx" />
                </div>
              </div>
              <datalist id="customer-suggestions">
                <option v-for="c in customerSuggestions" :key="c.name + c.phone" :value="c.name" />
              </datalist>

              <div>
                <label class="sf-label">إيميل العميل (اختياري)</label>
                <input v-model="form.customer_email" type="email" class="sf-field" placeholder="example@email.com" />
              </div>

              <div class="sf-form-grid">
                <div>
                  <label class="sf-label">اسم المهندس</label>
                  <input
                    v-model="form.engineer_name"
                    @change="onEngineerNameChange"
                    list="engineer-suggestions"
                    type="text"
                    class="sf-field"
                    placeholder="اسم المهندس"
                  />
                </div>
                <div>
                  <label class="sf-label">هاتف المهندس</label>
                  <input v-model="form.engineer_phone" type="text" class="sf-field" placeholder="05xxxxxxxx" />
                </div>
              </div>
              <datalist id="engineer-suggestions">
                <option v-for="e in engineerSuggestions" :key="e.name + e.phone" :value="e.name" />
              </datalist>

              <div>
                <label class="sf-label">الموقع (اختياري)</label>
                <input v-model="form.location" type="text" class="sf-field" placeholder="عنوان المشروع" />
              </div>

              <div>
                <label class="sf-label">ملاحظات</label>
                <textarea v-model="form.notes" rows="6" class="sf-field sf-field--textarea min-h-[160px]"></textarea>
              </div>

              <p v-if="formError" class="text-red-600 text-sm">{{ formError }}</p>

              <div class="sf-actions border-t border-gray-200 pt-4">
                <button
                  type="submit"
                  :disabled="submitting"
                  class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors"
                >
                  {{ submitting ? 'جاري الحفظ...' : (editingId ? 'حفظ التعديل' : 'إضافة الموعد') }}
                </button>
                <button type="button" class="border border-gray-300 px-6 py-3 rounded-lg text-sm text-gray-600 hover:bg-gray-50" @click="cancelForm">
                  إلغاء
                </button>
              </div>
            </form>
          </template>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue'
import api from '@/lib/api'
import { whatsappLink, mailtoLink } from '@/lib/contact'

interface Slot {
  id: number
  date: string
  start_time: string
  end_time: string | null
  status: string
  customer_name: string | null
  customer_phone: string | null
  customer_email: string | null
  engineer_name: string | null
  engineer_phone: string | null
  type: 'visit' | 'maintenance'
  location: string | null
  notes: string | null
  study_request?: { id: number } | null
}

interface Contact {
  name: string
  phone: string
  email?: string | null
}

const weekdayLabels = ['إث', 'ثلا', 'أرب', 'خم', 'جم', 'سبت', 'أحد']

const toDateStr = (d: Date) => {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

const todayStr = toDateStr(new Date())

const cursor = ref(new Date())
const slots = ref<Slot[]>([])
const loading = ref(true)

const monthLabel = computed(() => cursor.value.toLocaleDateString('ar-EG', { month: 'long', year: 'numeric' }))

const gridStart = computed(() => {
  const year = cursor.value.getFullYear()
  const month = cursor.value.getMonth()
  const firstOfMonth = new Date(year, month, 1)
  const offset = (firstOfMonth.getDay() + 6) % 7 // Monday-first grid
  return new Date(year, month, 1 - offset)
})

const slotsByDate = computed(() => {
  const map: Record<string, Slot[]> = {}
  for (const s of slots.value) {
    if (!map[s.date]) map[s.date] = []
    map[s.date].push(s)
  }
  for (const key in map) {
    map[key].sort((a, b) => a.start_time.localeCompare(b.start_time))
  }
  return map
})

const calendarDays = computed(() => {
  const month = cursor.value.getMonth()
  const days = []
  for (let i = 0; i < 42; i++) {
    const d = new Date(gridStart.value)
    d.setDate(gridStart.value.getDate() + i)
    const dateStr = toDateStr(d)
    days.push({
      date: dateStr,
      dayNum: d.getDate(),
      inCurrentMonth: d.getMonth() === month,
      isToday: dateStr === todayStr,
      isPast: dateStr < todayStr,
      appointments: slotsByDate.value[dateStr] || [],
    })
  }
  return days
})

const fetchSlots = async () => {
  loading.value = true
  try {
    const from = toDateStr(gridStart.value)
    const end = new Date(gridStart.value)
    end.setDate(end.getDate() + 41)
    const to = toDateStr(end)
    const res = await api.get('/admin/appointments', { params: { from, to } })
    slots.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const changeMonth = (delta: number) => {
  const next = new Date(cursor.value)
  next.setMonth(next.getMonth() + delta, 1)
  cursor.value = next
  fetchSlots()
}

// --- contacts autocomplete ---
const customerSuggestions = ref<Contact[]>([])
const engineerSuggestions = ref<Contact[]>([])

const fetchContacts = async () => {
  try {
    const res = await api.get('/admin/appointments/contacts')
    customerSuggestions.value = res.data.customers || []
    engineerSuggestions.value = res.data.engineers || []
  } catch (err) {
    console.error(err)
  }
}

const onCustomerNameChange = () => {
  const match = customerSuggestions.value.find((c) => c.name === form.customer_name)
  if (match && !form.customer_phone) form.customer_phone = match.phone || ''
  if (match && !form.customer_email) form.customer_email = match.email || ''
}

const onEngineerNameChange = () => {
  const match = engineerSuggestions.value.find((e) => e.name === form.engineer_name)
  if (match && !form.engineer_phone) form.engineer_phone = match.phone || ''
}

// --- day modal ---
const selectedDate = ref<string | null>(null)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const formError = ref('')
const submitting = ref(false)

const addDaysStr = (n: number) => {
  const d = new Date()
  d.setDate(d.getDate() + n)
  return toDateStr(d)
}

const setFormDate = (n: number) => {
  form.date = addDaysStr(n)
}

const emptyForm = (date?: string) => ({
  date: date || todayStr,
  start_time: '',
  end_time: '',
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  engineer_name: '',
  engineer_phone: '',
  type: 'visit' as 'visit' | 'maintenance',
  location: '',
  notes: '',
})

const form = reactive(emptyForm())

const dayAppointments = computed(() => (selectedDate.value ? slotsByDate.value[selectedDate.value] || [] : []))
const isPastSelectedDate = computed(() => !!selectedDate.value && selectedDate.value < todayStr)

const openDayModal = (date: string) => {
  selectedDate.value = date
  showForm.value = false
  editingId.value = null
  formError.value = ''
  Object.assign(form, emptyForm(date))
}

const closeDayModal = () => {
  selectedDate.value = null
}

const startAdd = () => {
  Object.assign(form, emptyForm(selectedDate.value || todayStr))
  editingId.value = null
  formError.value = ''
  showForm.value = true
}

const startEdit = (appt: Slot) => {
  Object.assign(form, {
    date: appt.date,
    start_time: appt.start_time?.slice(0, 5) ?? '',
    end_time: appt.end_time ? appt.end_time.slice(0, 5) : '',
    customer_name: appt.customer_name ?? '',
    customer_phone: appt.customer_phone ?? '',
    customer_email: appt.customer_email ?? '',
    engineer_name: appt.engineer_name ?? '',
    engineer_phone: appt.engineer_phone ?? '',
    type: appt.type ?? 'visit',
    location: appt.location ?? '',
    notes: appt.notes ?? '',
  })
  editingId.value = appt.id
  formError.value = ''
  showForm.value = true
}

const cancelForm = () => {
  showForm.value = false
  editingId.value = null
  formError.value = ''
}

const hasClientOverlap = () => {
  if (!form.start_time || !form.end_time) return false
  const dayAppts = slotsByDate.value[form.date] || []
  return dayAppts.some((a) => {
    if (editingId.value && a.id === editingId.value) return false
    const aStart = a.start_time.slice(0, 5)
    const aEnd = (a.end_time || '23:59').slice(0, 5)
    return form.start_time < aEnd && aStart < form.end_time
  })
}

const submitForm = async () => {
  formError.value = ''

  if (!form.date) {
    formError.value = 'يرجى تحديد التاريخ'
    return
  }
  if (form.date < todayStr) {
    formError.value = 'لا يمكن اختيار تاريخ سابق'
    return
  }
  if (!form.start_time || !form.end_time) {
    formError.value = 'يرجى تحديد وقت البداية والنهاية'
    return
  }
  if (form.start_time >= form.end_time) {
    formError.value = 'وقت النهاية يجب أن يكون بعد وقت البداية'
    return
  }
  if (form.start_time < '09:00' || form.end_time > '19:00') {
    formError.value = 'المواعيد متاحة من الساعة 9 صباحاً حتى 7 مساءً فقط'
    return
  }
  if (hasClientOverlap()) {
    formError.value = 'هذا الوقت متداخل مع موعد آخر في نفس اليوم'
    return
  }

  submitting.value = true
  try {
    let saved: Slot
    if (editingId.value) {
      const res = await api.patch(`/admin/appointments/${editingId.value}`, { ...form })
      const idx = slots.value.findIndex((s) => s.id === editingId.value)
      saved = res.data
      if (idx !== -1) slots.value[idx] = saved
      else slots.value.push(saved)
    } else {
      const res = await api.post('/admin/appointments', { ...form })
      saved = res.data
      slots.value.push(saved)
    }

    // Jump the calendar/modal to the appointment's date so the user sees it
    const savedMonth = new Date(saved.date + 'T00:00:00')
    if (savedMonth.getFullYear() !== cursor.value.getFullYear() || savedMonth.getMonth() !== cursor.value.getMonth()) {
      cursor.value = savedMonth
      await fetchSlots()
    }
    selectedDate.value = saved.date

    await fetchContacts()
    cancelForm()
  } catch (err: any) {
    const errors = err.response?.data?.errors
    formError.value = err.response?.data?.message
      || (errors ? Object.values(errors).flat()[0] as string : null)
      || 'حدث خطأ، حاول مرة أخرى'
  } finally {
    submitting.value = false
  }
}

const deleteAppt = async (appt: Slot) => {
  if (!confirm('حذف هذا الموعد نهائياً؟')) return
  try {
    await api.delete(`/admin/appointments/${appt.id}`)
    slots.value = slots.value.filter((s) => s.id !== appt.id)
  } catch (err) {
    alert('تعذر حذف الموعد')
  }
}

const formatTime = (time: string) => {
  const [h, m] = time.split(':').map(Number)
  const period = h >= 12 ? 'م' : 'ص'
  const hour12 = h % 12 === 0 ? 12 : h % 12
  return `${hour12}:${String(m).padStart(2, '0')} ${period}`
}

const formatFullDate = (dateStr: string) => {
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('ar-EG', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

const appointmentMessage = (appt: Slot) => {
  const typeLabel = appt.type === 'maintenance' ? 'صيانة' : 'زيارة'
  const timeRange = appt.end_time ? `${formatTime(appt.start_time)} - ${formatTime(appt.end_time)}` : formatTime(appt.start_time)
  let msg = `مرحباً${appt.customer_name ? ' ' + appt.customer_name : ''}، هذا تأكيد لموعد ${typeLabel} يوم ${formatFullDate(appt.date)} الساعة ${timeRange}.`
  if (appt.engineer_name) msg += ` سيقوم بالزيارة المهندس ${appt.engineer_name}.`
  if (appt.location) msg += ` الموقع: ${appt.location}.`
  msg += ' فريق SmartFlow.'
  return msg
}

onMounted(() => {
  fetchSlots()
  fetchContacts()
})
</script>

<style scoped>
.quick-date-btn {
  padding: 0.3rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #4b5563;
  transition: all 0.15s ease;
}

.quick-date-btn:hover {
  background: #f9fafb;
}

.quick-date-btn--active {
  background: var(--sf-accent, #1d4f91);
  color: #fff;
  border-color: var(--sf-accent, #1d4f91);
}
</style>



