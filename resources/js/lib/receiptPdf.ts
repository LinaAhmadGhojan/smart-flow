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

/**
 * Capture the visible receipt into an A5 PDF entirely in the browser.
 * Does not need Chrome on the server — works on shared hosting.
 *
 * Clones the receipt into the parent document with its styles/fonts so
 * html-to-image (SVG foreignObject) paints Arabic with the browser engine.
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

  const source =
    (srcDoc.querySelector('.receipt-page') as HTMLElement | null) ||
    (srcDoc.body.firstElementChild as HTMLElement | null)

  if (!source) {
    throw new Error('لم يتم العثور على محتوى الوصل')
  }

  const mount = document.createElement('div')
  mount.setAttribute('data-receipt-pdf-mount', '1')
  mount.style.cssText = [
    'position:fixed',
    'left:-10000px',
    'top:0',
    `width:${RECEIPT_W}px`,
    `height:${RECEIPT_H}px`,
    'overflow:hidden',
    'background:#ffffff',
    'z-index:-1',
    'pointer-events:none',
  ].join(';')

  // Bring @font-face + receipt CSS into the parent document for the clone.
  srcDoc.querySelectorAll('style').forEach((styleEl) => {
    mount.appendChild(styleEl.cloneNode(true))
  })

  const shell = document.createElement('div')
  shell.className = 'page-wrap'
  shell.dir = 'rtl'
  shell.style.cssText = `width:${RECEIPT_W}px;height:${RECEIPT_H}px;padding:0;margin:0;background:#fff;`

  const clone = source.cloneNode(true) as HTMLElement
  clone.classList.add('receipt-page')
  clone.style.cssText = [
    `width:${RECEIPT_W}px`,
    `height:${RECEIPT_H}px`,
    'max-width:none',
    'min-height:0',
    'margin:0',
    'background:#ffffff',
    'display:flex',
    'flex-direction:column',
    'overflow:hidden',
    'box-sizing:border-box',
  ].join(';')

  shell.appendChild(clone)
  mount.appendChild(shell)
  document.body.appendChild(mount)

  // SVG foreignObject often shrinks % widths — force full-width tables in px.
  const contentW = RECEIPT_W - 44 // receipt-page horizontal padding (22+22)
  const innerW = contentW - 32 // .sheet horizontal padding (16+16)
  const sheet = clone.querySelector('.sheet') as HTMLElement | null
  if (sheet) {
    sheet.style.width = `${contentW}px`
    sheet.style.maxWidth = `${contentW}px`
    sheet.style.boxSizing = 'border-box'
  }
  clone.querySelectorAll<HTMLElement>('table.header, table.fline, table.pay-grid, table.grid, table.signs').forEach((table) => {
    const isInsideSheet = !!table.closest('.sheet')
    const w = isInsideSheet ? innerW : contentW
    table.style.width = `${w}px`
    table.style.minWidth = `${w}px`
    table.style.maxWidth = `${w}px`
    table.style.tableLayout = 'fixed'
  })

  try {
    // Let cloned @font-face register in the parent document.
    if (document.fonts?.ready) {
      await document.fonts.ready
    }
    await new Promise((r) => setTimeout(r, 200))

    const dataUrl = await toPng(clone, {
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
  } finally {
    mount.remove()
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
