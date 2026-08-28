<template>
  <div class="flex items-center gap-1">
    <button
      v-for="star in 5"
      :key="star"
      type="button"
      :disabled="readonly"
      :class="[
        'text-2xl transition-transform',
        readonly ? 'cursor-default' : 'cursor-pointer hover:scale-110',
        star <= displayValue ? 'text-yellow-400' : 'text-gray-300',
      ]"
      @click="!readonly && emit('update:value', star)"
      @mouseenter="!readonly && (hovered = star)"
      @mouseleave="!readonly && (hovered = 0)"
      :aria-label="`${star} نجوم`"
    >★</button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  value: number
  readonly?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:value', v: number): void
}>()

const hovered = ref(0)

const displayValue = computed(() =>
  props.readonly ? props.value : (hovered.value || props.value)
)
</script>
