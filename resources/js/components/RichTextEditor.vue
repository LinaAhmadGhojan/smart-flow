<template>
  <div class="rte" dir="rtl">
    <QuillEditor
      v-model:content="content"
      content-type="html"
      theme="snow"
      :toolbar="toolbar"
      :placeholder="placeholder || 'اكتب الشروط هنا أو الصق من Word…'"
      class="rte-quill"
      @update:content="onUpdate"
    />
    <p class="rte-hint">يمكنك النسخ من Word واللصق هنا — استخدم الشريط أعلاه للتنسيق (غامق، مائل، قوائم، روابط…).</p>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const props = defineProps<{
  modelValue: string
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const content = ref(props.modelValue || '')

const toolbar = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline'],
  ['link'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['blockquote'],
  ['clean'],
]

watch(
  () => props.modelValue,
  (val) => {
    if ((val || '') !== (content.value || '')) {
      content.value = val || ''
    }
  },
)

const onUpdate = (html: string) => {
  const next = html === '<p><br></p>' ? '' : html
  emit('update:modelValue', next)
}
</script>

<style scoped>
.rte {
  border: 1px solid #d1d5db;
  border-radius: 0.75rem;
  overflow: hidden;
  background: #fff;
}

.rte :deep(.ql-toolbar.ql-snow) {
  border: 0;
  border-bottom: 1px solid #e5e7eb;
  background: #f8fafc;
  padding: 0.45rem 0.55rem;
  direction: ltr;
  text-align: left;
}

.rte :deep(.ql-container.ql-snow) {
  border: 0;
  font-size: 0.95rem;
  min-height: 160px;
  max-height: 360px;
  direction: rtl;
  text-align: right;
}

.rte :deep(.ql-editor) {
  min-height: 160px;
  max-height: 360px;
  overflow-y: auto;
  line-height: 1.85;
  padding: 0.85rem 1rem;
  direction: rtl;
  text-align: right;
  font-family: inherit;
}

.rte :deep(.ql-editor.ql-blank::before) {
  right: 1rem;
  left: auto;
  font-style: normal;
  color: #9ca3af;
}

.rte :deep(.ql-editor strong),
.rte :deep(.ql-editor b) {
  font-weight: 800;
  color: #123a72;
}

.rte :deep(.ql-snow .ql-stroke) {
  stroke: #1e3a5f;
}

.rte :deep(.ql-snow .ql-fill) {
  fill: #1e3a5f;
}

.rte :deep(.ql-snow .ql-picker) {
  color: #1e3a5f;
}

.rte :deep(.ql-toolbar button:hover),
.rte :deep(.ql-toolbar button.ql-active) {
  color: #2563eb;
}

.rte-hint {
  margin: 0;
  padding: 0.4rem 0.75rem 0.55rem;
  font-size: 0.72rem;
  color: #64748b;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
}
</style>
