import { downloadBlob, fetchAdminHtml, fetchAdminPdf } from '@/lib/api'
import { toPng } from 'html-to-image'
import { jsPDF } from 'jspdf'

export function reportHtmlPath(reportId: number | string): string {
  return `/admin/reports/${reportId}/html`
}

export function reportPdfPath(reportId: number | string): string {
  return `/admin/reports/${reportId}/pdf`
}

export function reportPdfFilename(reportId: number | string, reportNo?: string): string {
  return `${reportNo || `report-${reportId}`}.pdf`
}

/** A4 portrait in CSS px (210mm × 297mm @ 96dpi). */
const REPORT_W = 794
const REPORT_H = 1123

const SERVER_PDF_MODE_KEY = 'report-pdf-server-mode'

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

function reportFrameIsReady(frame: HTMLIFrameElement | null | undefined): frame is HTMLIFrameElement {
  return Boolean(frame?.contentDocument?.querySelector('.sheet'))
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

async function prepareReportDocument(doc: Document, quick = false): Promise<void> {
  if (doc.fonts?.ready) {
    await doc.fonts.ready
  }
  await waitForImages(doc)
  if (!quick) {
    await new Promise((r) => setTimeout(r, 400))
  }
}

async function isChromeServerPdf(blob: Blob): Promise<boolean> {
  const head = await blob.slice(0, 8192).text()
  if (head.includes('dompdf')) return false
  if (head.includes('jsPDF')) return false
  return head.includes('Skia') || head.includes('Chrome') || blob.size > 80000
}

async function downloadReportPdfFromFrame(
  frame: HTMLIFrameElement,
  filename: string,
  quick = false,
): Promise<void> {
  const srcDoc = frame.contentDocument
  if (!srcDoc?.body) {
    throw new Error('تعذر قراءة التقرير من الصفحة')
  }

  await prepareReportDocument(srcDoc, quick)

  const page = srcDoc.querySelector('.sheet') as HTMLElement | null
  if (!page) {
    throw new Error('لم يتم العثور على محتوى التقرير')
  }

  const rect = page.getBoundingClientRect()
  const width = Math.max(Math.round(rect.width), REPORT_W)
  const height = Math.max(Math.round(rect.height), REPORT_H)

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

async function captureReportFrame(
  frame: HTMLIFrameElement | null | undefined,
  filename: string,
  quick = false,
): Promise<boolean> {
  if (!reportFrameIsReady(frame)) {
    return false
  }

  await downloadReportPdfFromFrame(frame, filename, quick)
  return true
}

async function tryServerReportPdf(
  reportId: number | string,
  filename: string,
): Promise<'downloaded' | Blob | null> {
  try {
    const blob = await fetchAdminPdf(reportPdfPath(reportId))
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
 * Export report PDF to match the on-screen preview (same approach as payment receipts).
 */
export async function exportReportPdf(
  reportId: number | string,
  reportNo?: string,
  frame?: HTMLIFrameElement | null,
): Promise<void> {
  const filename = reportPdfFilename(reportId, reportNo)
  const serverMode = getServerPdfMode()

  if (reportFrameIsReady(frame)) {
    await captureReportFrame(frame, filename, true)
    return
  }

  let dompdfFallback: Blob | null = null

  if (serverMode === 'chrome') {
    const result = await tryServerReportPdf(reportId, filename)
    if (result === 'downloaded') {
      return
    }
    dompdfFallback = result instanceof Blob ? result : null
  } else if (serverMode === 'unknown') {
    const result = await tryServerReportPdf(reportId, filename)
    if (result === 'downloaded') {
      return
    }
    dompdfFallback = result instanceof Blob ? result : null
  }

  const html = await fetchAdminHtml(reportHtmlPath(reportId))
  const hiddenFrame = document.createElement('iframe')
  hiddenFrame.style.cssText =
    'position:fixed;left:-9999px;top:0;width:820px;height:1160px;border:0;opacity:0;pointer-events:none'
  hiddenFrame.title = 'تقرير زيارة موقع'
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
      await prepareReportDocument(doc)
    }
    if (await captureReportFrame(hiddenFrame, filename)) {
      return
    }
  } finally {
    hiddenFrame.remove()
  }

  if (dompdfFallback) {
    downloadBlob(dompdfFallback, filename)
    return
  }

  const blob = await fetchAdminPdf(reportPdfPath(reportId))
  downloadBlob(blob, filename)
}

export async function downloadReportPdf(
  reportId: number | string,
  reportNo?: string,
): Promise<void> {
  await exportReportPdf(reportId, reportNo)
}
