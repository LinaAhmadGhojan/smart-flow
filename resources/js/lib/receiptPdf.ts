import { downloadBlob, fetchAdminPdf } from '@/lib/api'
import { toPng } from 'html-to-image'
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

/** A5 landscape in CSS px (210mm × 148mm @ 96dpi). */
const RECEIPT_W = 794
const RECEIPT_H = 559

/**
 * Capture the receipt iframe exactly as painted on screen → A5 PDF.
 * Uses the browser SVG/foreignObject pipeline so Arabic + Cairo fonts stay joined.
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

  const prev = {
    width: page.style.width,
    height: page.style.height,
    maxWidth: page.style.maxWidth,
    minHeight: page.style.minHeight,
    overflow: page.style.overflow,
    background: page.style.background,
    margin: page.style.margin,
  }

  page.style.width = `${RECEIPT_W}px`
  page.style.height = `${RECEIPT_H}px`
  page.style.maxWidth = `${RECEIPT_W}px`
  page.style.minHeight = `${RECEIPT_H}px`
  page.style.overflow = 'hidden'
  page.style.background = '#ffffff'
  page.style.margin = '0 auto'

  await new Promise((r) => setTimeout(r, 300))

  try {
    const dataUrl = await toPng(page, {
      width: RECEIPT_W,
      height: RECEIPT_H,
      canvasWidth: RECEIPT_W * 2,
      canvasHeight: RECEIPT_H * 2,
      pixelRatio: 2,
      cacheBust: true,
      backgroundColor: '#ffffff',
      skipAutoScale: true,
      style: {
        width: `${RECEIPT_W}px`,
        height: `${RECEIPT_H}px`,
        margin: '0',
        transform: 'none',
      },
    })

    const pdf = new jsPDF({
      orientation: 'landscape',
      unit: 'mm',
      format: 'a5',
      compress: true,
    })

    pdf.addImage(
      dataUrl,
      'PNG',
      0,
      0,
      pdf.internal.pageSize.getWidth(),
      pdf.internal.pageSize.getHeight(),
      undefined,
      'FAST',
    )
    pdf.save(filename)
  } finally {
    page.style.width = prev.width
    page.style.height = prev.height
    page.style.maxWidth = prev.maxWidth
    page.style.minHeight = prev.minHeight
    page.style.overflow = prev.overflow
    page.style.background = prev.background
    page.style.margin = prev.margin
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
