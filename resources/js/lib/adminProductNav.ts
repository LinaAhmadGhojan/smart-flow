export interface ProductNavItem {
  id: number
  name: string
  name_ar: string
}

const STORAGE_KEY = 'admin_product_nav'

export function saveProductNavList(items: ProductNavItem[]): void {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items))
  } catch {
    // ignore storage errors
  }
}

export function readProductNavList(): ProductNavItem[] {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY)
    if (!raw) return []
    const parsed = JSON.parse(raw)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
}

export function getNextProduct(currentId: number, list: ProductNavItem[]): ProductNavItem | null {
  const idx = list.findIndex((p) => p.id === currentId)
  if (idx < 0 || idx >= list.length - 1) return null
  return list[idx + 1]
}
