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

/**
 * Capture the on-screen receipt (same HTML/CSS the user sees) into an A5
 * landscape PDF. This matches the web view pixel-for-pixel and avoids the
 * slow/fragile server Chrome → Dompdf path.
 */
export async function downloadReceiptPdfFromFrame(
  frame: HTMLIFrameElement,
  filename: string,
): Promise<void> {
  const doc = frame.contentDocument
  if (!doc?.body) {
    throw new Error('تعذر قراءة الوصل من الصفحة')
  }

  const fonts = doc.fonts
  if (fonts?.ready) {
    await fonts.ready
  }

  const page =
    (doc.querySelector('.receipt-page') as HTMLElement | null) ||
    (doc.body.firstElementChild as HTMLElement | null) ||
    doc.body

  const canvas = await html2canvas(page, {
    scale: 2,
    useCORS: true,
    allowTaint: true,
    backgroundColor: '#ffffff',
    logging: false,
    windowWidth: page.scrollWidth || 794,
    windowHeight: page.scrollHeight || 559,
  })

  // A5 landscape: 210mm × 148mm
  const pdf = new jsPDF({
    orientation: 'landscape',
    unit: 'mm',
    format: 'a5',
    compress: true,
  })

  const pageW = pdf.internal.pageSize.getWidth()
  const pageH = pdf.internal.pageSize.getHeight()
  const imgData = canvas.toDataURL('image/png')
  pdf.addImage(imgData, 'PNG', 0, 0, pageW, pageH, undefined, 'FAST')
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
