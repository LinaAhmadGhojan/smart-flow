<template>
  <div class="chat-assistant" dir="rtl">
    <Transition name="chat-panel">
      <div v-if="open" class="chat-panel" :class="{ 'chat-panel--expanded': expanded }" role="dialog" aria-label="مساعد SmartFlow">
        <header class="chat-head">
          <div>
            <p class="chat-head-title">مساعد SmartFlow</p>
            <p class="chat-head-sub">{{ chatModeLabel }}</p>
          </div>
          <div class="chat-head-actions">
            <button
              type="button"
              class="chat-head-btn"
              :aria-label="expanded ? 'تصغير المحادثة' : 'تكبير المحادثة'"
              @click="toggleExpanded"
            >
              <svg v-if="!expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
              </svg>
            </button>
            <button type="button" class="chat-head-btn" aria-label="إغلاق" @click="open = false">×</button>
          </div>
        </header>

        <div ref="scrollEl" class="chat-messages">
          <div
            v-for="(msg, i) in messages"
            :key="i"
            class="chat-bubble-wrap"
            :class="msg.role === 'user' ? 'chat-bubble-wrap--user' : 'chat-bubble-wrap--bot'"
          >
            <div class="chat-bubble" :class="msg.role === 'user' ? 'chat-bubble--user' : 'chat-bubble--bot'">
              {{ msg.text }}
            </div>
            <div v-if="msg.actions?.length" class="chat-actions">
              <a
                v-for="(act, j) in msg.actions"
                :key="j"
                :href="act.href.startsWith('http') ? act.href : act.href"
                :target="act.href.startsWith('http') ? '_blank' : undefined"
                :rel="act.href.startsWith('http') ? 'noopener noreferrer' : undefined"
                class="chat-action-btn"
                @click="onActionClick($event, act.href)"
              >
                {{ act.label }}
              </a>
            </div>
          </div>
          <div v-if="loading" class="chat-bubble-wrap chat-bubble-wrap--bot">
            <div class="chat-bubble chat-bubble--bot chat-typing">...</div>
          </div>
        </div>

        <div class="chat-quick">
          <button
            v-for="q in quickReplies"
            :key="q.text"
            type="button"
            class="chat-quick-btn"
            @click="sendQuick(q.text)"
          >
            {{ q.label }}
          </button>
        </div>

        <form class="chat-input-row" @submit.prevent="send">
          <input
            v-model="input"
            type="text"
            class="chat-input"
            placeholder="اكتب سؤالك..."
            maxlength="500"
            :disabled="loading"
          />
          <button type="submit" class="chat-send" :disabled="loading || !input.trim()" aria-label="إرسال">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </form>
      </div>
    </Transition>

    <button
      type="button"
      class="chat-fab"
      :class="{ 'chat-fab--open': open }"
      aria-label="فتح المحادثة"
      @click="toggle"
    >
      <svg v-if="!open" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
      <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/lib/api'

interface ChatAction {
  label: string
  href: string
}

interface ChatMessage {
  role: 'user' | 'bot'
  text: string
  actions?: ChatAction[]
}

const router = useRouter()
const open = ref(false)
const input = ref('')
const loading = ref(false)
const scrollEl = ref<HTMLElement | null>(null)
const greeted = ref(false)
const aiPowered = ref(false)
const expanded = ref(localStorage.getItem('sf-chat-expanded') === '1')

const toggleExpanded = () => {
  expanded.value = !expanded.value
  localStorage.setItem('sf-chat-expanded', expanded.value ? '1' : '0')
}

const chatModeLabel = computed(() =>
  aiPowered.value ? 'ذكاء اصطناعي · يفهم أسئلتك' : 'رد فوري · مجاني',
)

const messages = ref<ChatMessage[]>([])

const quickReplies = [
  { label: 'بيت ذكي', text: 'كيف يمكنني جعل بيتي سمارت؟' },
  { label: 'الخدمات', text: 'ما هي خدماتكم؟' },
  { label: 'الدعم', text: 'كيف يمكنني التواصل مع فريق الدعم' },
  { label: 'واتساب', text: 'تواصل واتساب' },
]

const scrollToBottom = async () => {
  await nextTick()
  const el = scrollEl.value
  if (el) el.scrollTop = el.scrollHeight
}

const pushBot = (text: string, actions?: ChatAction[]) => {
  messages.value.push({ role: 'bot', text, actions })
  scrollToBottom()
}

const greet = async () => {
  if (greeted.value) return
  greeted.value = true
  loading.value = true
  try {
    const res = await api.post('/chat', { message: 'مرحبا', locale: 'ar' })
    aiPowered.value = Boolean(res.data.ai)
    pushBot(res.data.reply || '', res.data.actions)
  } catch {
    pushBot(
      'مرحباً بك في SmartFlow 👋 كيف يمكننا مساعدتك؟',
      [
        { label: 'دراسة مشروع', href: '/project-study' },
        { label: 'واتساب', href: 'https://wa.me/971562566232?text=' + encodeURIComponent('مرحباً، أتواصل معكم من موقع SmartFlow') },
      ],
    )
  } finally {
    loading.value = false
  }
}

const toggle = () => {
  open.value = !open.value
  if (open.value && !greeted.value) greet()
}

const sendText = async (text: string) => {
  const trimmed = text.trim()
  if (!trimmed || loading.value) return

  messages.value.push({ role: 'user', text: trimmed })
  input.value = ''
  loading.value = true
  await scrollToBottom()

  try {
    const res = await api.post('/chat', {
      message: trimmed,
      locale: /[\u0600-\u06FF]/.test(trimmed) ? 'ar' : 'en',
    })
    aiPowered.value = Boolean(res.data.ai)
    pushBot(res.data.reply || '', res.data.actions)
  } catch {
    pushBot(
      'تعذّر الاتصال بالخادم مؤقتاً. يمكنك التواصل معنا مباشرة على واتساب أو تصفّح المنتجات.',
      [
        { label: 'واتساب', href: 'https://wa.me/971562566232?text=' + encodeURIComponent('مرحباً، أتواصل معكم من موقع SmartFlow') },
        { label: 'المنتجات', href: '/products' },
        { label: 'دراسة مشروع', href: '/project-study' },
      ],
    )
  } finally {
    loading.value = false
  }
}

const send = () => sendText(input.value)
const sendQuick = (text: string) => sendText(text)

const onActionClick = (e: MouseEvent, href: string) => {
  if (href.startsWith('http')) return
  e.preventDefault()
  open.value = false
  router.push(href)
}

onMounted(() => {
  /* optional: auto-greet after 8s if user hasn't opened */
})
</script>

<style scoped>
.chat-assistant {
  position: fixed;
  bottom: 1.25rem;
  left: 1.25rem;
  z-index: 9990;
  font-family: 'Cairo', 'Outfit', system-ui, sans-serif;
}

.chat-fab {
  width: 3.75rem;
  height: 3.75rem;
  border-radius: 9999px;
  border: none;
  background: linear-gradient(135deg, var(--sf-accent, #1d4f91), var(--sf-navy, #12327d));
  color: #fff;
  box-shadow: 0 8px 24px rgba(29, 79, 145, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.chat-fab:hover {
  transform: scale(1.05);
}

.chat-fab--open {
  background: #64748b;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.chat-panel {
  position: absolute;
  bottom: calc(3.75rem + 0.75rem);
  left: 0;
  width: min(26rem, calc(100vw - 2rem));
  height: min(34rem, calc(100vh - 7rem));
  max-height: min(34rem, calc(100vh - 7rem));
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.18);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width 0.25s ease, height 0.25s ease, max-height 0.25s ease;
}

.chat-panel--expanded {
  width: min(32rem, calc(100vw - 2rem));
  height: min(42rem, calc(100vh - 5rem));
  max-height: min(42rem, calc(100vh - 5rem));
}

.chat-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.85rem 1rem;
  background: linear-gradient(135deg, var(--sf-accent, #1d4f91), var(--sf-navy, #12327d));
  color: #fff;
}

.chat-head-title {
  font-weight: 700;
  font-size: 1rem;
  margin: 0;
}

.chat-head-sub {
  font-size: 0.75rem;
  opacity: 0.85;
  margin: 0.15rem 0 0;
}

.chat-head-actions {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.chat-head-btn {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #fff;
  width: 1.85rem;
  height: 1.85rem;
  border-radius: 0.5rem;
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.chat-head-btn:hover {
  background: rgba(255, 255, 255, 0.28);
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 0.85rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  min-height: 0;
  background: #f8fafc;
}

.chat-panel--expanded .chat-messages {
  padding: 1rem;
}

.chat-panel--expanded .chat-bubble {
  font-size: 0.9rem;
  padding: 0.65rem 0.85rem;
}

.chat-panel--expanded .chat-head-title {
  font-size: 1.1rem;
}

.chat-bubble-wrap {
  display: flex;
  flex-direction: column;
  max-width: 92%;
}

.chat-bubble-wrap--user {
  align-self: flex-end;
  align-items: flex-end;
}

.chat-bubble-wrap--bot {
  align-self: flex-start;
  align-items: flex-start;
}

.chat-bubble {
  padding: 0.55rem 0.75rem;
  border-radius: 0.85rem;
  font-size: 0.8125rem;
  line-height: 1.5;
  white-space: pre-wrap;
}

.chat-bubble--user {
  background: var(--sf-accent, #1d4f91);
  color: #fff;
  border-bottom-left-radius: 0.25rem;
}

.chat-bubble--bot {
  background: #fff;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  border-bottom-right-radius: 0.25rem;
}

.chat-typing {
  letter-spacing: 0.2em;
}

.chat-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-top: 0.4rem;
}

.chat-action-btn {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.35rem 0.6rem;
  border-radius: 9999px;
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
  text-decoration: none;
  transition: background 0.15s;
}

.chat-action-btn:hover {
  background: #d1fae5;
}

.chat-quick {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  padding: 0.5rem 0.75rem;
  border-top: 1px solid #f1f5f9;
  background: #fff;
}

.chat-quick-btn {
  font-size: 0.68rem;
  padding: 0.3rem 0.55rem;
  border-radius: 9999px;
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #475569;
  cursor: pointer;
}

.chat-quick-btn:hover {
  border-color: var(--sf-accent, #1d4f91);
  color: var(--sf-accent, #1d4f91);
}

.chat-input-row {
  display: flex;
  gap: 0.35rem;
  padding: 0.65rem 0.75rem;
  border-top: 1px solid #e2e8f0;
  background: #fff;
}

.chat-input {
  flex: 1;
  border: 1px solid #e2e8f0;
  border-radius: 0.65rem;
  padding: 0.5rem 0.65rem;
  font-size: 0.8125rem;
  outline: none;
}

.chat-input:focus {
  border-color: var(--sf-accent, #1d4f91);
  box-shadow: 0 0 0 2px rgba(29, 79, 145, 0.15);
}

.chat-send {
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  border: none;
  border-radius: 0.65rem;
  background: var(--sf-accent, #1d4f91);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.chat-send:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.chat-panel-enter-active,
.chat-panel-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.chat-panel-enter-from,
.chat-panel-leave-to {
  opacity: 0;
  transform: translateY(8px) scale(0.98);
}

@media (max-width: 480px) {
  .chat-assistant {
    left: 0.75rem;
    bottom: 0.75rem;
  }

  .chat-panel {
    width: calc(100vw - 1.5rem);
    height: min(34rem, calc(100vh - 6rem));
    max-height: min(34rem, calc(100vh - 6rem));
  }

  .chat-panel--expanded {
    width: calc(100vw - 1rem);
    height: calc(100vh - 5.5rem);
    max-height: calc(100vh - 5.5rem);
    bottom: calc(3.75rem + 0.5rem);
  }
}
</style>
