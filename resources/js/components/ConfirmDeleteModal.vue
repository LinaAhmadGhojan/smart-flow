<template>
  <Teleport to="body">
    <div v-if="open" class="sf-modal-backdrop" dir="rtl" @click.self="emitCancel">
      <div class="sf-modal-panel max-w-md text-center">
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
          </svg>
        </div>
        <p class="text-lg font-bold text-gray-900 mb-2">{{ title }}</p>
        <p v-if="message" class="text-sm text-gray-500 mb-4">{{ message }}</p>
        <p class="text-sm text-gray-600 mb-2">
          للتأكيد اكتب كلمة
          <span class="font-bold text-red-600 mx-1">{{ confirmWord }}</span>
          في الحقل أدناه
        </p>
        <input
          ref="inputEl"
          v-model="typed"
          type="text"
          class="sf-field text-center mb-5 tracking-wide"
          :placeholder="confirmWord"
          autocomplete="off"
          @keyup.enter="tryConfirm"
        />
        <div class="flex gap-3">
          <button
            type="button"
            class="flex-1 border border-gray-300 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50"
            :disabled="loading"
            @click="emitCancel"
          >
            إلغاء
          </button>
          <button
            type="button"
            class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white py-2.5 rounded-lg text-sm font-medium"
            :disabled="loading || !matches"
            @click="tryConfirm"
          >
            {{ loading ? 'جاري الحذف...' : 'تأكيد الحذف' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'

const props = withDefaults(
  defineProps<{
    open: boolean
    title?: string
    message?: string
    confirmWord?: string
    loading?: boolean
  }>(),
  {
    title: 'تأكيد الحذف؟',
    message: '',
    confirmWord: 'حذف',
    loading: false,
  },
)

const emit = defineEmits<{
  cancel: []
  confirm: []
}>()

const typed = ref('')
const inputEl = ref<HTMLInputElement | null>(null)

const matches = computed(() => typed.value.trim() === props.confirmWord)

watch(
  () => props.open,
  async (isOpen) => {
    typed.value = ''
    if (isOpen) {
      await nextTick()
      inputEl.value?.focus()
    }
  },
)

const emitCancel = () => {
  if (props.loading) return
  typed.value = ''
  emit('cancel')
}

const tryConfirm = () => {
  if (!matches.value || props.loading) return
  emit('confirm')
}
</script>
