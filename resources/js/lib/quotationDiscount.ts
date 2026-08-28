/** Split document-level discount across line amounts proportionally. */
export function allocateGlobalDiscount(globalDiscount: number, lineAmounts: number[]): number[] {
  const count = lineAmounts.length
  if (count === 0 || globalDiscount <= 0) {
    return Array(count).fill(0)
  }

  const subtotal = lineAmounts.reduce((s, n) => s + n, 0)
  if (subtotal <= 0) {
    return Array(count).fill(0)
  }

  const shares: number[] = []
  let allocated = 0
  lineAmounts.forEach((amount) => {
    const share = Math.round(globalDiscount * (amount / subtotal) * 100) / 100
    shares.push(share)
    allocated += share
  })

  const diff = Math.round((globalDiscount - allocated) * 100) / 100
  if (count > 0 && Math.abs(diff) >= 0.01) {
    shares[count - 1] = Math.round((shares[count - 1] + diff) * 100) / 100
  }

  return shares
}

export function computeGlobalDiscount(
  subtotal: number,
  type: '' | 'percent' | 'fixed',
  value: number | null | undefined
): number {
  const val = Number(value || 0)
  if (!type || !val || subtotal <= 0) return 0
  if (type === 'percent') return Math.min(subtotal, Math.round(subtotal * Math.min(100, val) / 100 * 100) / 100)
  return Math.min(subtotal, Math.round(val * 100) / 100)
}
