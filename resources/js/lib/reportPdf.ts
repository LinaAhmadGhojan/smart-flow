import { fetchAdminHtml } from '@/lib/api'
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

function reportFrameIsReady(frame: HTMLIFrameElement | null | undefined): frame is HTMLIFrameElement {
  return Boolean(frame?.contentDocument?.querySelector('.sheet'))
}

function prepareReportForCapture(doc: Document): HTMLElement {
  doc.body.classList.add('report-capture')

  const pageWrap = doc.querySelector('.page-wrap') as HTMLElement | null
  if (pageWrap) {
    pageWrap.style.background = '#ffffff'
    pageWrap.style.padding = '0'
    pageWrap.style.minHeight = 'auto'
    pageWrap.style.display = 'block'
  }

  const page = doc.querySelector('.sheet') as HTMLElement | null
  if (!page) {
    throw new Error('لم يتم العثور على محتوى التقرير')
  }

  page.style.width = `${REPORT_W}px`
  page.style.maxWidth = `${REPORT_W}px`
  page.style.overflow = 'visible'
  page.style.minHeight = `${REPORT_H}px`

  return page
}

function addReportImageToPdf(pdf: jsPDF, dataUrl: string, imgPxW: number, imgPxH: number): void {
  const pageW = pdf.internal.pageSize.getWidth()
  const pageH = pdf.internal.pageSize.getHeight()
  const imgHmm = (imgPxH / imgPxW) * pageW
  let yOffset = 0
  let pageIndex = 0

  while (yOffset < imgHmm - 0.5) {
    if (pageIndex > 0) {
      pdf.addPage()
    }
    pdf.addImage(dataUrl, 'PNG', 0, -yOffset, pageW, imgHmm, undefined, 'FAST')
    yOffset += pageH
    pageIndex += 1
  }
}

async function prepareReportDocument(doc: Document): Promise<void> {
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

  await new Promise((r) => setTimeout(r, 600))
}

function createHiddenReportFrame(html: string): HTMLIFrameElement {
  const frame = document.createElement('iframe')
  frame.style.cssText =
    'position:fixed;left:-9999px;top:0;width:794px;height:1200px;border:0;opacity:0;pointer-events:none'
  frame.title = 'تقرير زيارة موقع'
  frame.srcdoc = html
  document.body.appendChild(frame)

  return frame
}

async function waitForReportFrame(frame: HTMLIFrameElement): Promise<void> {
  await new Promise<void>((resolve) => {
    const done = () => resolve()
    frame.addEventListener('load', done, { once: true })
    setTimeout(done, 2500)
  })

  const doc = frame.contentDocument
  if (doc) {
    await prepareReportDocument(doc)
  }
}

async function downloadReportPdfFromFrame(frame: HTMLIFrameElement, filename: string): Promise<void> {
  const srcDoc = frame.contentDocument
  if (!srcDoc?.body) {
    throw new Error('تعذر قراءة التقرير من الصفحة')
  }

  await prepareReportDocument(srcDoc)
  const page = prepareReportForCapture(srcDoc)

  const width = REPORT_W
  const height = Math.max(page.scrollHeight, page.offsetHeight, REPORT_H)

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

  addReportImageToPdf(pdf, dataUrl, width, height)
  pdf.save(filename)

  srcDoc.body.classList.remove('report-capture')
}

async function captureReportFrame(
  frame: HTMLIFrameElement | null | undefined,
  filename: string,
): Promise<boolean> {
  if (!reportFrameIsReady(frame)) {
    return false
  }

  try {
    await downloadReportPdfFromFrame(frame, filename)
    return true
  } catch {
    return false
  }
}

/**
 * Export report PDF — captures the same HTML as the preview (client-side, like delivery note).
 */
export async function exportReportPdf(
  reportId: number | string,
  reportNo?: string,
  frame?: HTMLIFrameElement | null,
): Promise<void> {
  const filename = reportPdfFilename(reportId, reportNo)

  if (reportFrameIsReady(frame)) {
    const ok = await captureReportFrame(frame, filename)
    if (ok) return
  }

  const html = await fetchAdminHtml(reportHtmlPath(reportId))
  const hiddenFrame = createHiddenReportFrame(html)

  try {
    await waitForReportFrame(hiddenFrame)
    const ok = await captureReportFrame(hiddenFrame, filename)
    if (ok) return
  } finally {
    hiddenFrame.remove()
  }

  throw new Error('تعذر تصدير PDF — انتظر تحميل المعاينة ثم حاول مرة أخرى')
}

export async function downloadReportPdf(
  reportId: number | string,
  reportNo?: string,
): Promise<void> {
  await exportReportPdf(reportId, reportNo)
}
