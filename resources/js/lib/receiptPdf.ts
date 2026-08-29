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

function saveCanvasAsA5Pdf(canvas: HTMLCanvasElement, filename: string): void {
  const pdf = new jsPDF({
    orientation: 'landscape',
    unit: 'mm',
    format: 'a5',
    compress: true,
  })
  pdf.addImage(
    canvas.toDataURL('image/jpeg', 0.97),
    'JPEG',
    0,
    0,
    pdf.internal.pageSize.getWidth(),
    pdf.internal.pageSize.getHeight(),
    undefined,
    'FAST',
  )
  pdf.save(filename)
}

function canvasLooksBroken(canvas: HTMLCanvasElement): boolean {
  const ctx = canvas.getContext('2d')
  if (!ctx || canvas.width < 10 || canvas.height < 10) {
    return true
  }
  const { data } = ctx.getImageData(0, 0, Math.min(60, canvas.width), Math.min(60, canvas.height))
  let nonWhite = 0
  for (let i = 0; i < data.length; i += 4) {
    if (data[i] < 250 || data[i + 1] < 250 || data[i + 2] < 250) {
      nonWhite++
    }
  }
  return nonWhite < 12
}

/**
 * Capture the visible receipt iframe into an A5 landscape PDF.
 * Uses foreignObjectRendering first so the browser paints Arabic as on screen
 * (html2canvas's own text engine breaks Arabic joining).
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
    margin: page.style.margin,
  }

  page.style.width = `${RECEIPT_W}px`
  page.style.height = `${RECEIPT_H}px`
  page.style.maxWidth = `${RECEIPT_W}px`
  page.style.minHeight = `${RECEIPT_H}px`
  page.style.overflow = 'hidden'
  page.style.background = '#ffffff'
  page.style.margin = '0 auto'

  await new Promise((r) => setTimeout(r, 250))

  try {
    let canvas = await html2canvas(page, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
      // Critical for Arabic: let the browser paint, don't re-layout glyphs.
      foreignObjectRendering: true,
      logging: false,
      width: RECEIPT_W,
      height: RECEIPT_H,
      windowWidth: RECEIPT_W,
      windowHeight: RECEIPT_H,
      scrollX: 0,
      scrollY: 0,
      x: 0,
      y: 0,
      onclone: (_doc, el) => {
        el.style.letterSpacing = 'normal'
        el.querySelectorAll<HTMLElement>('*').forEach((node) => {
          node.style.letterSpacing = 'normal'
          node.style.wordSpacing = 'normal'
        })
      },
    })

    if (canvasLooksBroken(canvas)) {
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
        scrollX: 0,
        scrollY: 0,
        x: 0,
        y: 0,
        onclone: (_doc, el) => {
          el.style.letterSpacing = 'normal'
          el.querySelectorAll<HTMLElement>('*').forEach((node) => {
            node.style.letterSpacing = 'normal'
            node.style.wordSpacing = 'normal'
          })
        },
      })
    }

    if (canvasLooksBroken(canvas)) {
      throw new Error('تعذر التقاط الوصل من الشاشة')
    }

    saveCanvasAsA5Pdf(canvas, filename)
  } finally {
    page.style.width = previous.width
    page.style.height = previous.height
    page.style.maxWidth = previous.maxWidth
    page.style.minHeight = previous.minHeight
    page.style.overflow = previous.overflow
    page.style.background = previous.background
    page.style.margin = previous.margin
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
