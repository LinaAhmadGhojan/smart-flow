import { computed, ref, watch } from 'vue'

export type Locale = 'ar' | 'en'

const STORAGE_KEY = 'smartflow_locale'

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

const dict = {
  home: { ar: 'الرئيسية', en: 'Home' },
  offers: { ar: 'العروض', en: 'Offers' },
  products: { ar: 'المنتجات', en: 'Products' },
  groups: { ar: 'المجموعات', en: 'Groups' },
  about: { ar: 'من نحن', en: 'About' },
  projectStudy: { ar: 'دراسة مشروع', en: 'Project Study' },
  gateMachineStudy: { ar: 'دراسة ماكينة باب', en: 'Gate Machine Study' },
  whatsapp: { ar: 'واتساب', en: 'WhatsApp' },
  viewProducts: { ar: 'اكتشف منتجاتنا', en: 'View Products' },
  contactUs: { ar: 'تواصل معنا', en: 'Contact Us' },
  heroTitleLine1: { ar: 'حلول', en: 'Integrated' },
  heroTitleAccent: { ar: 'أمان\u00A0وتحكم', en: 'Security & Control' },
  heroTitleLine2: { ar: 'ذكي لمنزلك', en: 'for Your Home' },
  heroSubtitle: {
    ar: 'سمارت فلو شركة متخصصة في أنظمة الأبواب الأوتوماتيكية، كاميرات المراقبة، والبيوت الذكية في دولة الإمارات. نوفر منتجات أصلية، تركيباً احترافياً، ضماناً لمدة سنة، ودعماً فنياً مستمراً — لراحة بالك وحماية منزلك وعملك.',
    en: 'SmartFlow specializes in automatic gate systems, CCTV surveillance, and smart home solutions across the UAE. We deliver genuine products, professional installation, a full one-year warranty, and ongoing technical support — so your home and business stay protected around the clock.',
  },
  heroFeature1: { ar: 'ماكينات أبواب سلايد وسوينغ', en: 'Sliding & swing gate motors' },
  heroFeature2: { ar: 'كاميرات مراقبة وأنظمة أمنية', en: 'CCTV & security systems' },
  heroFeature3: { ar: 'منزل ذكي وتحكم عن بُعد', en: 'Smart home & remote control' },
  heroFeature4: { ar: 'تركيب مجاني وضمان سنة كاملة', en: 'Free installation & 1-year warranty' },
  heroBrowseOffers: { ar: 'استعرض العروض', en: 'Browse Offers' },
  ourProducts: { ar: 'منتجاتنا', en: 'Our Products' },
  ourGroups: { ar: 'مجموعات المنتجات', en: 'Product Groups' },
  groupsSubtitle: {
    ar: 'اختر مجموعة لاستعراض المنتجات بداخلها',
    en: 'Choose a group to browse products inside',
  },
  productsSubtitle: {
    ar: 'أحدث الحلول الذكية لمنزلك وعملك',
    en: 'Latest smart solutions for your home and business',
  },
  inStock: { ar: 'متوفر', en: 'In Stock' },
  outOfStock: { ar: 'غير متوفر', en: 'Out of Stock' },
  orderWhatsapp: { ar: 'اطلب عبر واتساب', en: 'Order via WhatsApp' },
  tapToContact: { ar: 'اضغط للتواصل معنا مباشرة', en: 'Tap to contact us directly' },
  loadMore: { ar: 'عرض المزيد', en: 'Load More' },
  loading: { ar: 'جاري التحميل...', en: 'Loading...' },
  aboutUs: { ar: 'من نحن', en: 'About Us' },
  yearsExp: { ar: 'سنة خبرة', en: 'Years of Experience' },
  happyClients: { ar: 'عميل سعيد', en: 'Happy Clients' },
  projectsDone: { ar: 'مشروع منجز', en: 'Projects Completed' },
  viewGroup: { ar: 'عرض المجموعة', en: 'View Group' },
  productsInGroup: { ar: 'منتجات المجموعة', en: 'Group Products' },
  backToGroups: { ar: 'العودة للمجموعات', en: 'Back to Groups' },
  noProducts: { ar: 'لا توجد منتجات في هذه المجموعة حالياً', en: 'No products in this group yet' },
  allProducts: { ar: 'كل المنتجات', en: 'All Products' },
  language: { ar: 'العربية', en: 'English' },
  heroBadge: {
    ar: 'شريكك الموثوق للحلول الذكية في الإمارات 🤝',
    en: 'Your trusted smart solutions partner in the UAE 🤝',
  },
  heroTrust: { ar: '+500 تركيب ناجح', en: '500+ Successful Installs' },
  heroTrustSub: { ar: 'في جميع أنحاء الإمارات', en: 'Across the UAE' },
  specialOffers: { ar: 'عروضنا الخاصة', en: 'Special Offers' },
  offersSubtitle: {
    ar: 'احصل على أفضل الحلول الذكية بأسعار حصرية — تركيب مجاني وضمان سنة',
    en: 'Get the best smart solutions at exclusive prices — free installation & 1-year warranty',
  },
  viewDetails: { ar: 'التفاصيل', en: 'Details' },
  saveAmount: { ar: 'وفر', en: 'Save' },
  noOffers: { ar: 'لا توجد عروض متاحة حالياً', en: 'No offers available at the moment' },
  viewAllOffers: { ar: 'عرض كل العروض', en: 'View All Offers' },
  limitedOffer: { ar: 'عرض محدود', en: 'Limited Offer' },
} as const

export type DictKey = keyof typeof dict

export function useLocale() {
  const isAr = computed(() => locale.value === 'ar')
  const isEn = computed(() => locale.value === 'en')

  const t = (key: DictKey) => dict[key][locale.value]

  const toggleLocale = () => {
    locale.value = locale.value === 'ar' ? 'en' : 'ar'
  }

  const setLocale = (next: Locale) => {
    locale.value = next
  }

  const localized = <T extends { name?: string; name_ar?: string }>(
    item: T,
    enKey: keyof T = 'name' as keyof T,
    arKey: keyof T = 'name_ar' as keyof T,
  ) => {
    const ar = item[arKey]
    const en = item[enKey]
    if (locale.value === 'ar') return (ar as string) || (en as string) || ''
    return (en as string) || (ar as string) || ''
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
