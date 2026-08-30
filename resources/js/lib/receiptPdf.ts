import { downloadBlob, fetchAdminHtml, fetchAdminPdf } from '@/lib/api'
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

const SERVER_PDF_MODE_KEY = 'receipt-pdf-server-mode'

type ServerPdfMode = 'chrome' | 'dompdf' | 'unknown'

function getServerPdfMode(): ServerPdfMode {
  try {
    const mode = sessionStorage.getItem(SERVER_PDF_MODE_KEY)
    if (mode === 'chrome' || mode === 'dompdf') {
      return mode
    }
  } catch {
    // ignore storage errors
  }
  return 'unknown'
}

function rememberServerPdfMode(mode: ServerPdfMode): void {
  if (mode === 'unknown') {
    return
  }
  try {
    sessionStorage.setItem(SERVER_PDF_MODE_KEY, mode)
  } catch {
    // ignore storage errors
  }
}

function receiptFrameIsReady(frame: HTMLIFrameElement | null | undefined): frame is HTMLIFrameElement {
  return Boolean(frame?.contentDocument?.querySelector('.receipt-page'))
}

async function waitForImages(doc: Document): Promise<void> {
  const pending = Array.from(doc.querySelectorAll('img')).filter((img) => !img.complete)
  if (pending.length === 0) {
    return
  }

  await Promise.all(
    pending.map(
      (img) =>
        new Promise<void>((resolve) => {
          img.addEventListener('load', () => resolve(), { once: true })
          img.addEventListener('error', () => resolve(), { once: true })
        }),
    ),
  )
}

async function prepareReceiptDocument(doc: Document, quick = false): Promise<void> {
  if (doc.fonts?.ready) {
    await doc.fonts.ready
  }
  await waitForImages(doc)
  if (!quick) {
    await new Promise((r) => setTimeout(r, 150))
  }
}

async function isChromeServerPdf(blob: Blob): Promise<boolean> {
  const head = await blob.slice(0, 8192).text()
  if (head.includes('dompdf')) return false
  if (head.includes('jsPDF')) return false
  return head.includes('Skia') || head.includes('Chrome') || blob.size > 55000
}

/**
 * Capture the receipt iframe exactly as painted on screen → A5 PDF.
 */
export async function downloadReceiptPdfFromFrame(
  frame: HTMLIFrameElement,
  filename: string,
  quick = false,
): Promise<void> {
  const srcDoc = frame.contentDocument
  if (!srcDoc?.body) {
    throw new Error('تعذر قراءة الوصل من الصفحة')
  }

  await prepareReceiptDocument(srcDoc, quick)

  const page =
    (srcDoc.querySelector('.receipt-page') as HTMLElement | null) ||
    (srcDoc.body.firstElementChild as HTMLElement | null)

  if (!page) {
    throw new Error('لم يتم العثور على محتوى الوصل')
  }

  const rect = page.getBoundingClientRect()
  const width = Math.max(Math.round(rect.width), RECEIPT_W)
  const height = Math.max(Math.round(rect.height), RECEIPT_H)

  const dataUrl = await toPng(page, {
    width,
    height,
    canvasWidth: width * 2,
    canvasHeight: height * 2,
    pixelRatio: 2,
    cacheBust: true,
    backgroundColor: '#ffffff',
    skipAutoScale: true,
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
}

export async function downloadReceiptPdf(
  projectId: number | string,
  paymentId: number | string,
): Promise<void> {
  const blob = await fetchAdminPdf(paymentReceiptPdfPath(projectId, paymentId))
  downloadBlob(blob, paymentReceiptFilename(projectId, paymentId))
}

async function waitForReceiptFrame(frame: HTMLIFrameElement): Promise<void> {
  await new Promise<void>((resolve) => {
    const done = () => resolve()
    frame.addEventListener('load', done, { once: true })
    setTimeout(done, 1200)
  })

  const doc = frame.contentDocument
  if (doc) {
    await prepareReceiptDocument(doc)
  }
}

function createHiddenReceiptFrame(html: string): HTMLIFrameElement {
  const frame = document.createElement('iframe')
  frame.style.cssText =
    'position:fixed;left:-9999px;top:0;width:820px;height:640px;border:0;opacity:0;pointer-events:none'
  frame.title = 'وصل استلام مالي'
  frame.srcdoc = html
  document.body.appendChild(frame)

  return frame
}

async function captureReceiptFrame(
  frame: HTMLIFrameElement | null | undefined,
  filename: string,
  quick = false,
): Promise<boolean> {
  if (!receiptFrameIsReady(frame)) {
    return false
  }

  await downloadReceiptPdfFromFrame(frame, filename, quick)
  return true
}

async function tryServerReceiptPdf(
  projectId: number | string,
  paymentId: number | string,
  filename: string,
): Promise<'downloaded' | Blob | null> {
  try {
    const blob = await fetchAdminPdf(paymentReceiptPdfPath(projectId, paymentId))
    if (await isChromeServerPdf(blob)) {
      rememberServerPdfMode('chrome')
      downloadBlob(blob, filename)
      return 'downloaded'
    }

    rememberServerPdfMode('dompdf')
    return blob
  } catch {
    return null
  }
}

/**
 * Export receipt PDF to match /admin/payments/receipt/... preview.
 * Uses the visible iframe immediately when already loaded (fast path).
 */
export async function exportReceiptPdf(
  projectId: number | string,
  paymentId: number | string,
  frame?: HTMLIFrameElement | null,
): Promise<void> {
  const filename = paymentReceiptFilename(projectId, paymentId)
  const serverMode = getServerPdfMode()

  // Receipt preview page: iframe is already on screen — capture directly (no server wait).
  if (receiptFrameIsReady(frame)) {
    await captureReceiptFrame(frame, filename, true)
    return
  }

  let dompdfFallback: Blob | null = null

  if (serverMode === 'chrome') {
    const result = await tryServerReceiptPdf(projectId, paymentId, filename)
    if (result === 'downloaded') {
      return
    }
    dompdfFallback = result instanceof Blob ? result : null
  } else if (serverMode === 'unknown') {
    const result = await tryServerReceiptPdf(projectId, paymentId, filename)
    if (result === 'downloaded') {
      return
    }
    dompdfFallback = result instanceof Blob ? result : null
  }

  const html = await fetchAdminHtml(paymentReceiptHtmlPath(projectId, paymentId))
  const hiddenFrame = createHiddenReceiptFrame(html)

  try {
    await waitForReceiptFrame(hiddenFrame)
    if (await captureReceiptFrame(hiddenFrame, filename)) {
      return
    }
  } finally {
    hiddenFrame.remove()
  }

  if (dompdfFallback) {
    downloadBlob(dompdfFallback, filename)
    return
  }

  await downloadReceiptPdf(projectId, paymentId)
}

export function deliveryNoteHtmlPath(projectId: number | string, noteId: number | string): string {
  return `/admin/projects/${projectId}/delivery-notes/${noteId}/html`
}

export function deliveryNotePdfPath(projectId: number | string, noteId: number | string): string {
  return `/admin/projects/${projectId}/delivery-notes/${noteId}/pdf`
}

export function deliveryNotePdfFilename(number?: string): string {
  return `${number || 'delivery-note'}.pdf`
}

/** A4 portrait in CSS px (210mm × 297mm @ 96dpi). */
const DELIVERY_NOTE_W = 794
const DELIVERY_NOTE_H = 1123

const DN_SERVER_PDF_MODE_KEY = 'delivery-note-pdf-server-mode'

type DnServerPdfMode = 'chrome' | 'dompdf' | 'unknown'

function getDnServerPdfMode(): DnServerPdfMode {
  try {
    const mode = sessionStorage.getItem(DN_SERVER_PDF_MODE_KEY)
    if (mode === 'chrome' || mode === 'dompdf') {
      return mode
    }
  } catch {
    // ignore storage errors
  }
  return 'unknown'
}

function rememberDnServerPdfMode(mode: DnServerPdfMode): void {
  if (mode === 'unknown') {
    return
  }
  try {
    sessionStorage.setItem(DN_SERVER_PDF_MODE_KEY, mode)
  } catch {
    // ignore storage errors
  }
}

function deliveryNoteFrameIsReady(frame: HTMLIFrameElement | null | undefined): frame is HTMLIFrameElement {
  return Boolean(frame?.contentDocument?.querySelector('.sheet'))
}

async function prepareDeliveryNoteDocument(doc: Document, quick = false): Promise<void> {
  if (doc.fonts?.ready) {
    await doc.fonts.ready
  }

  const pending = Array.from(doc.querySelectorAll('img')).filter((img) => !img.complete)
  if (pending.length > 0) {
    await Promise.all(
      pending.map(
        (img) =>
          new Promise<void>((resolve) => {
            img.addEventListener('load', () => resolve(), { once: true })
            img.addEventListener('error', () => resolve(), { once: true })
          }),
      ),
    )
  }

  if (!quick) {
    await new Promise((r) => setTimeout(r, 400))
  }
}

async function isChromeDeliveryNotePdf(blob: Blob): Promise<boolean> {
  const head = await blob.slice(0, 8192).text()
  if (head.includes('dompdf')) return false
  if (head.includes('jsPDF')) return false
  return head.includes('Skia') || head.includes('Chrome') || blob.size > 80000
}

async function downloadDeliveryNotePdfFromFrame(
  frame: HTMLIFrameElement,
  filename: string,
  quick = false,
): Promise<void> {
  const srcDoc = frame.contentDocument
  if (!srcDoc?.body) {
    throw new Error('تعذر قراءة دليفري نوت من الصفحة')
  }

  await prepareDeliveryNoteDocument(srcDoc, quick)

  const page = srcDoc.querySelector('.sheet') as HTMLElement | null
  if (!page) {
    throw new Error('لم يتم العثور على محتوى دليفري نوت')
  }

  const rect = page.getBoundingClientRect()
  const width = Math.max(Math.round(rect.width), DELIVERY_NOTE_W)
  const height = Math.max(Math.round(rect.height), DELIVERY_NOTE_H)

  const dataUrl = await toPng(page, {
    width,
    height,
    canvasWidth: width * 2,
    canvasHeight: height * 2,
    pixelRatio: 2,
    cacheBust: true,
    backgroundColor: '#ffffff',
    skipAutoScale: true,
  })

  const pdf = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'a4',
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
}

async function captureDeliveryNoteFrame(
  frame: HTMLIFrameElement | null | undefined,
  filename: string,
  quick = false,
): Promise<boolean> {
  if (!deliveryNoteFrameIsReady(frame)) {
    return false
  }

  await downloadDeliveryNotePdfFromFrame(frame, filename, quick)
  return true
}

async function tryServerDeliveryNotePdf(
  projectId: number | string,
  noteId: number | string,
  filename: string,
): Promise<'downloaded' | Blob | null> {
  try {
    const blob = await fetchAdminPdf(deliveryNotePdfPath(projectId, noteId))
    if (await isChromeDeliveryNotePdf(blob)) {
      rememberDnServerPdfMode('chrome')
      downloadBlob(blob, filename)
      return 'downloaded'
    }

    rememberDnServerPdfMode('dompdf')
    return blob
  } catch {
    return null
  }
}

/**
 * Export delivery note PDF to match the on-screen preview.
 */
export async function exportDeliveryNotePdf(
  projectId: number | string,
  noteId: number | string,
  number?: string,
  frame?: HTMLIFrameElement | null,
): Promise<void> {
  const filename = deliveryNotePdfFilename(number)
  const serverMode = getDnServerPdfMode()

  if (deliveryNoteFrameIsReady(frame)) {
    await captureDeliveryNoteFrame(frame, filename, true)
    return
  }

  let dompdfFallback: Blob | null = null

  if (serverMode === 'chrome' || serverMode === 'unknown') {
    const result = await tryServerDeliveryNotePdf(projectId, noteId, filename)
    if (result === 'downloaded') {
      return
    }
    dompdfFallback = result instanceof Blob ? result : null
  }

  const html = await fetchAdminHtml(deliveryNoteHtmlPath(projectId, noteId))
  const hiddenFrame = document.createElement('iframe')
  hiddenFrame.style.cssText =
    'position:fixed;left:-9999px;top:0;width:820px;height:1160px;border:0;opacity:0;pointer-events:none'
  hiddenFrame.title = 'دليفري نوت'
  hiddenFrame.srcdoc = html
  document.body.appendChild(hiddenFrame)

  try {
    await new Promise<void>((resolve) => {
      const done = () => resolve()
      hiddenFrame.addEventListener('load', done, { once: true })
      setTimeout(done, 3000)
    })
    const doc = hiddenFrame.contentDocument
    if (doc) {
      await prepareDeliveryNoteDocument(doc)
    }
    if (await captureDeliveryNoteFrame(hiddenFrame, filename)) {
      return
    }
  } finally {
    hiddenFrame.remove()
  }

  if (dompdfFallback) {
    downloadBlob(dompdfFallback, filename)
    return
  }

  const blob = await fetchAdminPdf(deliveryNotePdfPath(projectId, noteId))
  downloadBlob(blob, filename)
}

export async function downloadDeliveryNotePdf(
  projectId: number | string,
  noteId: number | string,
  number?: string,
): Promise<void> {
  await exportDeliveryNotePdf(projectId, noteId, number)
}
