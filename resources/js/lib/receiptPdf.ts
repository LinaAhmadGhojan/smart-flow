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

const RECEIPT_W = 794
const RECEIPT_H = 559

function forceReceiptGeometry(root: HTMLElement): void {
  root.style.boxSizing = 'border-box'
  root.style.width = `${RECEIPT_W}px`
  root.style.height = `${RECEIPT_H}px`
  root.style.maxWidth = `${RECEIPT_W}px`
  root.style.overflow = 'hidden'

  const contentW = RECEIPT_W - 44 // page padding 22+22
  const innerW = contentW - 32 // sheet padding 16+16

  const sheet = root.querySelector('.sheet') as HTMLElement | null
  if (sheet) {
    sheet.style.width = `${contentW}px`
    sheet.style.maxWidth = `${contentW}px`
    sheet.style.boxSizing = 'border-box'
  }

  root.querySelectorAll<HTMLElement>('.fline').forEach((row) => {
    row.style.display = 'flex'
    row.style.flexDirection = 'row'
    row.style.alignItems = 'flex-end'
    row.style.width = `${innerW}px`
    row.style.maxWidth = `${innerW}px`
    row.style.boxSizing = 'border-box'
  })

  root.querySelectorAll<HTMLElement>('table.header, table.pay-grid, table.grid, table.signs').forEach((table) => {
    const insideSheet = !!table.closest('.sheet')
    const w = insideSheet ? innerW : contentW
    table.style.width = `${w}px`
    table.style.minWidth = `${w}px`
    table.style.maxWidth = `${w}px`
    table.style.tableLayout = 'fixed'
  })
}

/**
 * Capture the on-screen receipt (already laid out correctly) into an A5 PDF.
 * Captures the live iframe DOM — no re-clone that breaks RTL tables.
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

  forceReceiptGeometry(page)
  await new Promise((r) => setTimeout(r, 120))

  const dataUrl = await toPng(page, {
    width: RECEIPT_W,
    height: RECEIPT_H,
    canvasWidth: RECEIPT_W * 2,
    canvasHeight: RECEIPT_H * 2,
    pixelRatio: 2,
    cacheBust: true,
    backgroundColor: '#ffffff',
    style: {
      width: `${RECEIPT_W}px`,
      height: `${RECEIPT_H}px`,
      transform: 'none',
      direction: 'rtl',
    },
  })

  const pdf = new jsPDF({
    orientation: 'landscape',
    unit: 'mm',
    format: 'a5',
    compress: true,
  })

  const pageW = pdf.internal.pageSize.getWidth()
  const pageH = pdf.internal.pageSize.getHeight()
  pdf.addImage(dataUrl, 'PNG', 0, 0, pageW, pageH, undefined, 'FAST')
  pdf.save(filename)
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
