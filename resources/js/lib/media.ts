/** Normalize media paths so they work with Laravel public/ docroot (artisan serve). */
export function mediaUrl(path?: string | null, fallback = '/logo.jpeg'): string {
  if (!path) return fallback

  let url = path.trim()

  // Absolute remote URLs stay as-is
  if (/^https?:\/\//i.test(url)) return url

  // DB historically stored /public/storage/... (shared hosting root docroot)
  url = url.replace(/^\/public\/storage\//, '/storage/')

  // Old seeder paths
  url = url.replace(/^\/uploads\//, '/storage/')

  if (!url.startsWith('/')) url = `/${url}`

  return url
}

/** On <img> error, retry the alternate public/storage path once. */
export function handleMediaError(event: Event) {
  const img = event.target as HTMLImageElement
  if (!img || img.dataset.fallbackTried === '1') return

  img.dataset.fallbackTried = '1'
  const current = img.getAttribute('src') || ''

  if (current.includes('/storage/')) {
    img.src = current.replace('/storage/', '/public/storage/')
    return
  }

  if (current.includes('/public/storage/')) {
    img.src = current.replace('/public/storage/', '/storage/')
    return
  }

  img.src = '/logo.jpeg'
}
