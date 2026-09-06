import { computed, ref, watch } from 'vue'
import ar from '@/locales/ar.json'
import en from '@/locales/en.json'

export type Locale = 'ar' | 'en'

const STORAGE_KEY = 'smartflow_locale'

const messages: Record<Locale, Record<string, string>> = {
  ar: ar as Record<string, string>,
  en: en as Record<string, string>,
}

function readInitialLocale(): Locale {
  if (typeof window === 'undefined') return 'ar'
  const saved = localStorage.getItem(STORAGE_KEY)
  return saved === 'en' || saved === 'ar' ? saved : 'ar'
}

const locale = ref<Locale>(readInitialLocale())

function applyDocumentLocale(next: Locale) {
  if (typeof document === 'undefined') return
  document.documentElement.lang = next
  document.documentElement.dir = next === 'ar' ? 'rtl' : 'ltr'
}

applyDocumentLocale(locale.value)

watch(locale, (next) => {
  localStorage.setItem(STORAGE_KEY, next)
  applyDocumentLocale(next)
})

export type DictKey = string

export function useLocale() {
  const isAr = computed(() => locale.value === 'ar')
  const isEn = computed(() => locale.value === 'en')

  /** Translate UI string. Falls back to Arabic, then the key itself. */
  const t = (key: string): string => {
    const pack = messages[locale.value] || messages.ar
    return pack[key] ?? messages.ar[key] ?? key
  }

  const toggleLocale = () => {
    locale.value = locale.value === 'ar' ? 'en' : 'ar'
  }

  const setLocale = (next: Locale) => {
    locale.value = next === 'en' ? 'en' : 'ar'
  }

  const localized = <T extends { name?: string; name_ar?: string }>(
    item: T,
    enKey: keyof T = 'name' as keyof T,
    arKey: keyof T = 'name_ar' as keyof T,
  ) => {
    const arVal = item[arKey]
    const enVal = item[enKey]
    if (locale.value === 'ar') return (arVal as string) || (enVal as string) || ''
    return (enVal as string) || (arVal as string) || ''
  }

  return {
    locale,
    isAr,
    isEn,
    t,
    toggleLocale,
    setLocale,
    localized,
  }
}
