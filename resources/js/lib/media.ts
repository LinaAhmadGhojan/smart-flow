/** Normalize media paths for Hostinger (/public/storage) and local artisan serve (/storage). */
export function mediaUrl(path?: string | null, fallback = '/logo.jpeg'): string {
  if (!path) return fallback

  let url = path.trim()

  if (/^https?:\/\//i.test(url)) return url

  url = url.replace(/^\/uploads\//, '/storage/')
  url = url.replace(/^\/public\/storage\//, '/storage/')

  if (!url.startsWith('/storage/') && !url.startsWith('/')) {
    url = `/storage/${url}`
  } else if (!url.startsWith('/storage/') && !url.startsWith('/public/')) {
    url = url.startsWith('/') ? `/storage${url}` : `/storage/${url}`
  }

  // Hostinger docroot = public_html → need /public/storage
  // php artisan serve docroot = public → /storage
  const usePublicPrefix = typeof window !== 'undefined'
    && !/^https?:\/\/(127\.0\.0\.1|localhost)(:\d+)?$/i.test(window.location.origin)

  if (url.startsWith('/storage/')) {
    return usePublicPrefix ? `/public${url}` : url
  }

  return url
}

/** On <img> error, retry the alternate storage prefix once. */
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
