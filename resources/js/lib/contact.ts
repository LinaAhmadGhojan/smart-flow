/** Normalize a local (UAE-style) phone number into a wa.me-compatible digits-only number. */
export function toWhatsappDigits(phone?: string | null): string | null {
  if (!phone) return null
  let digits = phone.replace(/[^\d]/g, '')
  if (!digits) return null

  if (digits.startsWith('00')) digits = digits.slice(2)
  if (digits.startsWith('0')) digits = '971' + digits.slice(1)
  else if (digits.length <= 10 && !digits.startsWith('971')) digits = '971' + digits

  return digits
}

/** Build a wa.me link with an optional pre-filled message. */
export function whatsappLink(phone?: string | null, message = ''): string | null {
  const digits = toWhatsappDigits(phone)
  if (!digits) return null
  return message
    ? `https://wa.me/${digits}?text=${encodeURIComponent(message)}`
    : `https://wa.me/${digits}`
}

/** Build a mailto: link with an optional subject/body. */
export function mailtoLink(email?: string | null, subject = '', body = ''): string | null {
  if (!email) return null
  const params = new URLSearchParams()
  if (subject) params.set('subject', subject)
  if (body) params.set('body', body)
  const query = params.toString()
  return `mailto:${email}${query ? `?${query}` : ''}`
}
