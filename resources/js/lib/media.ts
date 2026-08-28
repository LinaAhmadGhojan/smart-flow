/** Normalize media paths for Hostinger (docroot = public_html → /public/storage/). */
export function mediaUrl(path?: string | null, fallback = '/logo.jpeg'): string {
  if (!path) return fallback

  let url = path.trim()

  if (/^https?:\/\//i.test(url)) return url

  url = url.replace(/^\/uploads\//, '/public/storage/')

  if (url.startsWith('/storage/')) {
    url = '/public' + url
  } else if (!url.startsWith('/public/storage/') && !url.startsWith('/')) {
    url = `/public/storage/${url}`
  } else if (!url.startsWith('/public/') && !url.startsWith('/')) {
    url = `/${url}`
  }

  return url
}

/** On <img> error, retry /storage/ path (local artisan serve) once. */
export function handleMediaError(event: Event) {
  const img = event.target as HTMLImageElement
  if (!img || img.dataset.fallbackTried === '1') return

  img.dataset.fallbackTried = '1'
  const current = img.getAttribute('src') || ''

  if (current.includes('/public/storage/')) {
    img.src = current.replace('/public/storage/', '/storage/')
    return
  }

  if (current.includes('/storage/')) {
    img.src = current.replace('/storage/', '/public/storage/')
    return
  }

  img.src = '/logo.jpeg'
}
