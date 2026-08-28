import { ref, computed } from 'vue'
import api from '@/lib/api'

export interface AdminNotificationItem {
  kind: 'project' | 'gate'
  id: number
  customer_name: string
  customer_phone: string
  location: string | null
  created_at: string
}

const POLL_MS = 45_000
const BASE_TITLE = 'SmartFlow — لوحة التحكم'

const newStudyRequests = ref(0)
const newGateStudies = ref(0)
const latest = ref<AdminNotificationItem[]>([])
const loading = ref(false)
const panelOpen = ref(false)
const browserPermission = ref<NotificationPermission>(
  typeof Notification !== 'undefined' ? Notification.permission : 'denied'
)

let pollTimer: ReturnType<typeof setInterval> | null = null
let previousTotal = -1
let started = false

const newTotal = computed(() => newStudyRequests.value + newGateStudies.value)

const kindLabel = (kind: AdminNotificationItem['kind']) =>
  kind === 'gate' ? 'ماكينة باب' : 'دراسة مشروع'

const updateDocumentTitle = () => {
  if (typeof document === 'undefined') return
  document.title = newTotal.value > 0 ? `(${newTotal.value}) ${BASE_TITLE}` : BASE_TITLE
}

const showBrowserNotification = (item: AdminNotificationItem) => {
  if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return
  const body = [kindLabel(item.kind), item.customer_name, item.location].filter(Boolean).join(' · ')
  try {
    const n = new Notification('طلب جديد — SmartFlow', {
      body,
      icon: '/logo.svg',
      tag: `sf-request-${item.kind}-${item.id}`,
    })
    n.onclick = () => {
      window.focus()
      window.location.href = '/admin/study-requests'
      n.close()
    }
  } catch {
    /* ignore */
  }
}

const notifyIfIncreased = (total: number, items: AdminNotificationItem[]) => {
  if (previousTotal < 0) {
    previousTotal = total
    return
  }
  if (total <= previousTotal) {
    previousTotal = total
    return
  }

  const newest = items[0]
  if (newest) showBrowserNotification(newest)
  previousTotal = total
}

const refresh = async () => {
  loading.value = true
  try {
    const res = await api.get('/admin/notifications/summary')
    const data = res.data || {}
    newStudyRequests.value = Number(data.new_study_requests) || 0
    newGateStudies.value = Number(data.new_gate_studies) || 0
    latest.value = Array.isArray(data.latest) ? data.latest : []
    notifyIfIncreased(newTotal.value, latest.value)
    updateDocumentTitle()
  } catch {
    /* silent — admin may be logging out */
  } finally {
    loading.value = false
  }
}

const startPolling = () => {
  if (started) return
  started = true
  refresh()
  pollTimer = setInterval(refresh, POLL_MS)
}

const stopPolling = () => {
  started = false
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

const requestBrowserPermission = async () => {
  if (typeof Notification === 'undefined') return false
  if (Notification.permission === 'granted') {
    browserPermission.value = 'granted'
    return true
  }
  if (Notification.permission === 'denied') {
    browserPermission.value = 'denied'
    return false
  }
  const result = await Notification.requestPermission()
  browserPermission.value = result
  return result === 'granted'
}

const togglePanel = () => {
  panelOpen.value = !panelOpen.value
}

const closePanel = () => {
  panelOpen.value = false
}

export function useAdminNotifications() {
  return {
    newTotal,
    newStudyRequests,
    newGateStudies,
    latest,
    loading,
    panelOpen,
    browserPermission,
    kindLabel,
    refresh,
    startPolling,
    stopPolling,
    requestBrowserPermission,
    togglePanel,
    closePanel,
  }
}
