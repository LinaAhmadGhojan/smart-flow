<template>
  <div class="appointment-calendar">
    <div class="flex items-center justify-between mb-4">
      <button
        type="button"
        class="cal-nav-btn"
        @click="changeMonth(-1)"
      >
        ‹ السابق
      </button>
      <h4 class="text-lg font-bold text-blue-900">{{ monthLabel }}</h4>
      <button
        type="button"
        class="cal-nav-btn"
        @click="changeMonth(1)"
      >
        التالي ›
      </button>
    </div>

    <div class="flex items-center gap-4 mb-4 text-sm">
      <span class="flex items-center gap-1.5">
        <span class="inline-block w-3.5 h-3.5 rounded bg-green-500"></span>
        متاح | Available
      </span>
      <span class="flex items-center gap-1.5">
        <span class="inline-block w-3.5 h-3.5 rounded bg-gray-300"></span>
        محجوز | Booked
      </span>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">
      جاري تحميل المواعيد... | Loading...
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
      {{ error }}
    </div>

    <div v-else-if="days.length === 0" class="text-center py-8 text-gray-500 text-sm">
      لا توجد مواعيد متاحة في هذا الشهر | No slots available this month
    </div>

    <div v-else class="space-y-4 max-h-96 overflow-y-auto pe-1">
      <div v-for="day in days" :key="day.date" class="border border-gray-200 rounded-lg p-3">
        <div class="font-semibold text-gray-800 mb-2 text-sm">{{ formatDayLabel(day.date) }}</div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="slot in day.slots"
            :key="slot.id"
            type="button"
            :disabled="slot.status === 'booked'"
            class="slot-btn"
            :class="{
              'slot-btn--available': slot.status === 'available' && selected !== slot.id,
              'slot-btn--selected': selected === slot.id,
              'slot-btn--booked': slot.status === 'booked',
            }"
            @click="selectSlot(slot)"
          >
            {{ formatTime(slot.start_time) }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/lib/api'

interface Slot {
  id: number
  date: string
  start_time: string
  end_time: string | null
  status: 'available' | 'booked'
}

const props = defineProps<{
  modelValue: number | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: number | null): void
  (e: 'slot-selected', slot: Slot | null): void
}>()

const slots = ref<Slot[]>([])
const loading = ref(false)
const error = ref('')
const cursor = ref(new Date())
const selected = ref<number | null>(props.modelValue)

watch(() => props.modelValue, (val) => {
  selected.value = val
})

const monthLabel = computed(() => {
  return cursor.value.toLocaleDateString('ar-EG', { month: 'long', year: 'numeric' })
})

const days = computed(() => {
  const grouped: Record<string, Slot[]> = {}
  for (const slot of slots.value) {
    if (!grouped[slot.date]) grouped[slot.date] = []
    grouped[slot.date].push(slot)
  }
  return Object.keys(grouped)
    .sort()
    .map((date) => ({ date, slots: grouped[date] }))
})

const formatDayLabel = (dateStr: string) => {
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('ar-EG', { weekday: 'long', day: 'numeric', month: 'long' })
}

const formatTime = (time: string) => {
  const [h, m] = time.split(':').map(Number)
  const period = h >= 12 ? 'م' : 'ص'
  const hour12 = h % 12 === 0 ? 12 : h % 12
  return `${hour12}:${String(m).padStart(2, '0')} ${period}`
}

const fetchSlots = async () => {
  loading.value = true
  error.value = ''
  try {
    const year = cursor.value.getFullYear()
    const month = String(cursor.value.getMonth() + 1).padStart(2, '0')
    const res = await api.get('/appointments', { params: { month: `${year}-${month}` } })
    slots.value = Array.isArray(res.data) ? res.data : []
  } catch (err: any) {
    error.value = err.response?.data?.message || 'تعذر تحميل المواعيد | Failed to load appointments'
  } finally {
    loading.value = false
  }
}

const changeMonth = (delta: number) => {
  const next = new Date(cursor.value)
  next.setMonth(next.getMonth() + delta)
  cursor.value = next
  fetchSlots()
}

const selectSlot = (slot: Slot) => {
  if (slot.status === 'booked') return
  if (selected.value === slot.id) {
    selected.value = null
    emit('update:modelValue', null)
    emit('slot-selected', null)
    return
  }
  selected.value = slot.id
  emit('update:modelValue', slot.id)
  emit('slot-selected', slot)
}

onMounted(fetchSlots)
</script>

<style scoped>
.cal-nav-btn {
  @apply text-sm text-blue-700 hover:text-blue-900 font-medium px-2 py-1 rounded hover:bg-blue-50 transition-colors;
}

.slot-btn {
  @apply px-3 py-2 rounded-lg text-sm font-medium border transition-colors;
}

.slot-btn--available {
  @apply bg-green-50 border-green-300 text-green-800 hover:bg-green-100;
}

.slot-btn--selected {
  @apply bg-blue-600 border-blue-600 text-white;
}

.slot-btn--booked {
  @apply bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed line-through;
}
</style>
