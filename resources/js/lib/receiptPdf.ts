import { downloadBlob, fetchAdminPdf } from '@/lib/api'
import html2canvas from 'html2canvas'
import { jsPDF } from 'jspdf'

export function paymentReceiptHtmlPath(projectId: number | string, paymentId: number | string): string {
  return `/admin/projects/${projectId}/payments/${paymentId}/html`
}

export function paymentReceiptPdfPath(projectId: number | string, paymentId: number | string): string {
  return `/admin/projects/${projectId}/payments/${paymentId}/pdf`
}

export function paymentReceiptFilename(projectId: number | string, paymentId: number | string): string {
  return `RCP-${projectId}-${String(paymentId).padStart(3, '0')}.pdf`
}

/** A5 landscape design size in CSS pixels (210mm × 148mm at 96dpi). */
const RECEIPT_W = 794
const RECEIPT_H = 559

function canvasLooksBlank(canvas: HTMLCanvasElement): boolean {
  const ctx = canvas.getContext('2d')
  if (!ctx) {
    return true
  }
  const { width, height } = canvas
  if (width < 10 || height < 10) {
    return true
  }
  const sample = ctx.getImageData(0, 0, Math.min(40, width), Math.min(40, height)).data
  let nonWhite = 0
  for (let i = 0; i < sample.length; i += 4) {
    if (sample[i] < 250 || sample[i + 1] < 250 || sample[i + 2] < 250) {
      nonWhite++
    }
  }
  return nonWhite < 8
}

/**
 * Capture the visible receipt into a real A5 landscape PDF download.
 * Uses the browser paint pipeline so Arabic + layout match the on-screen page.
 * Works on shared hosting (no Chrome on the server required).
 */
export async function downloadReceiptPdfFromFrame(
  frame: HTMLIFrameElement,
  filename: string,
): Promise<void> {
  const srcDoc = frame.contentDocument
  if (!srcDoc?.body) {
    throw new Error('تعذر قراءة الوصل من الصفحة')
  }

  if (srcDoc.fonts?.ready) {
    await srcDoc.fonts.ready
  }

  const page =
    (srcDoc.querySelector('.receipt-page') as HTMLElement | null) ||
    (srcDoc.body.firstElementChild as HTMLElement | null)

  if (!page) {
    throw new Error('لم يتم العثور على محتوى الوصل')
  }

  const previous = {
    width: page.style.width,
    height: page.style.height,
    maxWidth: page.style.maxWidth,
    minHeight: page.style.minHeight,
    overflow: page.style.overflow,
    background: page.style.background,
  }

  page.style.width = `${RECEIPT_W}px`
  page.style.height = `${RECEIPT_H}px`
  page.style.maxWidth = `${RECEIPT_W}px`
  page.style.minHeight = `${RECEIPT_H}px`
  page.style.overflow = 'hidden'
  page.style.background = '#ffffff'

  await new Promise((r) => setTimeout(r, 150))

  try {
    let canvas = await html2canvas(page, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
      foreignObjectRendering: true,
      logging: false,
      width: RECEIPT_W,
      height: RECEIPT_H,
      windowWidth: RECEIPT_W,
      windowHeight: RECEIPT_H,
    })

    if (canvasLooksBlank(canvas)) {
      canvas = await html2canvas(page, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff',
        foreignObjectRendering: false,
        logging: false,
        width: RECEIPT_W,
        height: RECEIPT_H,
        windowWidth: RECEIPT_W,
        windowHeight: RECEIPT_H,
      })
    }

    const pdf = new jsPDF({
      orientation: 'landscape',
      unit: 'mm',
      format: 'a5',
      compress: true,
    })

    // Exact A5 landscape: 210mm × 148mm
    pdf.addImage(
      canvas.toDataURL('image/jpeg', 0.96),
      'JPEG',
      0,
      0,
      pdf.internal.pageSize.getWidth(),
      pdf.internal.pageSize.getHeight(),
      undefined,
      'FAST',
    )
    pdf.save(filename)
  } finally {
    page.style.width = previous.width
    page.style.height = previous.height
    page.style.maxWidth = previous.maxWidth
    page.style.minHeight = previous.minHeight
    page.style.overflow = previous.overflow
    page.style.background = previous.background
  }
}

export async function downloadReceiptPdf(
  projectId: number | string,
  paymentId: number | string,
): Promise<void> {
  const blob = await fetchAdminPdf(paymentReceiptPdfPath(projectId, paymentId))
  downloadBlob(blob, paymentReceiptFilename(projectId, paymentId))
}

export function deliveryNoteHtmlPath(projectId: number | string, noteId: number | string): string {
  return `/admin/projects/${projectId}/delivery-notes/${noteId}/html`
}

export function deliveryNotePdfPath(projectId: number | string, noteId: number | string): string {
  return `/admin/projects/${projectId}/delivery-notes/${noteId}/pdf`
}

export async function downloadDeliveryNotePdf(
  projectId: number | string,
  noteId: number | string,
  number?: string,
): Promise<void> {
  const blob = await fetchAdminPdf(deliveryNotePdfPath(projectId, noteId))
  downloadBlob(blob, `${number || 'delivery-note'}.pdf`)
}
