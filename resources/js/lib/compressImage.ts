/** Resize + compress an image in the browser before upload (Hostinger-friendly). */
export async function compressImage(
  file: File,
  options: { maxWidth?: number; maxHeight?: number; quality?: number } = {},
): Promise<File> {
  const maxWidth = options.maxWidth ?? 1600
  const maxHeight = options.maxHeight ?? 1600
  const quality = options.quality ?? 0.82

  if (!file.type.startsWith('image/') || file.type === 'image/gif') {
    return file
  }

  // Already small enough — skip work
  if (file.size <= 400 * 1024) {
    return file
  }

  const bitmap = await createImageBitmap(file)
  try {
    let { width, height } = bitmap
    const scale = Math.min(1, maxWidth / width, maxHeight / height)
    width = Math.round(width * scale)
    height = Math.round(height * scale)

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    const ctx = canvas.getContext('2d')
    if (!ctx) return file

    ctx.drawImage(bitmap, 0, 0, width, height)

    const blob = await new Promise<Blob | null>((resolve) =>
      canvas.toBlob(resolve, 'image/jpeg', quality),
    )
    if (!blob || blob.size >= file.size) {
      return file
    }

    const base = file.name.replace(/\.[^.]+$/, '') || 'product'
    return new File([blob], `${base}.jpg`, { type: 'image/jpeg', lastModified: Date.now() })
  } finally {
    bitmap.close()
  }
}
