export interface SearchableProduct {
  id: number
  name?: string | null
  name_ar?: string | null
  brand?: string | null
  description?: string | null
  description_ar?: string | null
  features?: string[] | null
  category?: { name?: string | null; name_ar?: string | null } | null
}

export const productCode = (p: SearchableProduct) => (p.brand || `P${p.id}`).toString()

export const productSearchHaystack = (p: SearchableProduct): string => {
  const parts: string[] = [
    p.name ?? '',
    p.name_ar ?? '',
    p.brand ?? '',
    p.description ?? '',
    p.description_ar ?? '',
    productCode(p),
    String(p.id),
    p.category?.name ?? '',
    p.category?.name_ar ?? '',
  ]
  if (Array.isArray(p.features)) {
    parts.push(...p.features.map(String))
  }
  return parts.join(' ').toLowerCase()
}

export const filterProducts = <T extends SearchableProduct>(list: T[], q: string, limit = 40): T[] => {
  const term = q.trim().toLowerCase()
  if (!term) return list.slice(0, limit)
  return list.filter((p) => productSearchHaystack(p).includes(term)).slice(0, limit)
}

export const productDisplayName = (p: SearchableProduct) => (p.name_ar || p.name || '').trim()

export const productEnglishSubtitle = (p: SearchableProduct) => {
  const en = (p.name || '').trim()
  const ar = (p.name_ar || '').trim()
  return en && en !== ar ? en : ''
}
