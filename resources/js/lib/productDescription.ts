/** Remove "أبرز المميزات" / "Key Features" block from product description for quotes & invoices. */
export const stripFeaturesSection = (text: string): string => {
  const raw = text.trim()
  if (!raw) return ''

  for (const marker of ['أبرز المميزات', 'Key Features']) {
    const idx = raw.toLowerCase().indexOf(marker.toLowerCase())
    if (idx === -1) continue
    return raw
      .slice(0, idx)
      .replace(/[.\s:]+$/u, '')
      .trim()
  }

  return raw
}

export const productDetailForQuote = (descriptionAr?: string | null, description?: string | null) =>
  stripFeaturesSection((descriptionAr || description || '').trim())
