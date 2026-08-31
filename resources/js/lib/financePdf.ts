import { downloadBlob, fetchAdminHtml, fetchAdminPdf } from '@/lib/api'
import { toPng } from 'html-to-image'
import { jsPDF } from 'jspdf'

const DOC_W = 794

export function invoiceHtmlPath(invoiceId: number | string): string {
  return `/admin/invoices/${invoiceId}/html`
}

export function invoicePdfPath(invoiceId: number | string): string {
  return `/admin/invoices/${invoiceId}/pdf`
}

export function invoicePdfFilename(number?: string): string {
  return `${number || 'invoice'}.pdf`
}

export function quotationHtmlPath(quotationId: number | string): string {
  return `/admin/quotations/${quotationId}/html`
}

export function quotationPdfPath(quotationId: number | string): string {
  return `/admin/quotations/${quotationId}/pdf`
}

export function quotationPdfFilename(number?: string): string {
  return `${number || 'estimate'}.pdf`
}

async function waitForImages(doc: Document): Promise<void> {
  const pending = Array.from(doc.querySelectorAll('img')).filter((img) => !img.complete)
  if (!pending.length) return
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

async function prepareDocument(doc: Document): Promise<void> {
  if (doc.fonts?.ready) {
    await doc.fonts.ready
  }
  await waitForImages(doc)
  await new Promise((r) => setTimeout(r, 600))
}

function prepareSheetForCapture(doc: Document): HTMLElement {
  doc.body.classList.add('fd-capture')
  const pageWrap = doc.querySelector('.page-wrap') as HTMLElement | null
  if (pageWrap) {
    pageWrap.style.background = '#ffffff'
    pageWrap.style.padding = '0'
    pageWrap.style.minHeight = 'auto'
    pageWrap.style.display = 'block'
  }
  const sheet = doc.querySelector('.sheet') as HTMLElement | null
  if (!sheet) {
    throw new Error('لم يتم العثور على محتوى المستند')
  }
  sheet.style.width = `${DOC_W}px`
  sheet.style.maxWidth = `${DOC_W}px`
  sheet.style.overflow = 'visible'
  return sheet
}

function addTallImageToPdf(pdf: jsPDF, dataUrl: string, imgPxW: number, imgPxH: number): void {
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

async function captureHtmlToPdf(html: string, filename: string): Promise<void> {
  const frame = document.createElement('iframe')
  frame.style.cssText =
    'position:fixed;left:-9999px;top:0;width:820px;height:1400px;border:0;opacity:0;pointer-events:none'
  frame.srcdoc = html
  document.body.appendChild(frame)

  try {
    await new Promise<void>((resolve) => {
      const done = () => resolve()
      frame.addEventListener('load', done, { once: true })
      setTimeout(done, 2500)
    })

    const doc = frame.contentDocument
    if (!doc?.body) {
      throw new Error('تعذر تحميل المستند')
    }

    await prepareDocument(doc)
    const sheet = prepareSheetForCapture(doc)
    const width = DOC_W
    const height = Math.max(sheet.scrollHeight, sheet.offsetHeight, 1123)

    const dataUrl = await toPng(sheet, {
      width,
      height,
      canvasWidth: width * 2,
      canvasHeight: height * 2,
      pixelRatio: 2,
      cacheBust: true,
      backgroundColor: '#ffffff',
      skipAutoScale: true,
    })

    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4', compress: true })
    addTallImageToPdf(pdf, dataUrl, width, height)
    pdf.save(filename)
  } finally {
    frame.remove()
  }
}

async function tryServerChromePdf(path: string, filename: string): Promise<boolean> {
  try {
    const blob = await fetchAdminPdf(path)
    const head = await blob.slice(0, 8192).text()
    if (head.includes('dompdf') || head.includes('jsPDF')) {
      return false
    }
    if (head.includes('Skia') || head.includes('Chrome') || blob.size > 80000) {
      downloadBlob(blob, filename)
      return true
    }
  } catch {
    // fall through to client capture
  }
  return false
}

export async function exportInvoicePdf(invoiceId: number | string, number?: string): Promise<void> {
  const filename = invoicePdfFilename(number)
  if (await tryServerChromePdf(invoicePdfPath(invoiceId), filename)) {
    return
  }
  const html = await fetchAdminHtml(invoiceHtmlPath(invoiceId))
  await captureHtmlToPdf(html, filename)
}

export async function exportQuotationPdf(quotationId: number | string, number?: string): Promise<void> {
  const filename = quotationPdfFilename(number)
  if (await tryServerChromePdf(quotationPdfPath(quotationId), filename)) {
    return
  }
  const html = await fetchAdminHtml(quotationHtmlPath(quotationId))
  await captureHtmlToPdf(html, filename)
}
