import { downloadBlob, fetchAdminHtml, fetchAdminPdf } from '@/lib/api'
import { toPng } from 'html-to-image'
import { jsPDF } from 'jspdf'

const DOC_W = 794
const PAGE_H = 1123

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

function prepareCaptureRoot(doc: Document): void {
  doc.body.classList.add('fd-capture')
  const pageWrap = doc.querySelector('.page-wrap') as HTMLElement | null
  if (pageWrap) {
    pageWrap.style.background = '#ffffff'
    pageWrap.style.padding = '0'
    pageWrap.style.minHeight = 'auto'
    pageWrap.style.display = 'block'
  }
  doc.querySelectorAll('.sheet').forEach((sheet) => {
    const el = sheet as HTMLElement
    el.style.width = `${DOC_W}px`
    el.style.maxWidth = `${DOC_W}px`
    el.style.overflow = 'visible'
  })
}

async function captureElementPng(el: HTMLElement): Promise<{ dataUrl: string; width: number; height: number }> {
  const width = DOC_W
  const height = Math.max(el.scrollHeight, el.offsetHeight, PAGE_H)

  const dataUrl = await toPng(el, {
    width,
    height,
    canvasWidth: width * 2,
    canvasHeight: height * 2,
    pixelRatio: 2,
    cacheBust: true,
    backgroundColor: '#ffffff',
    skipAutoScale: true,
  })

  return { dataUrl, width, height }
}

function addPageImage(pdf: jsPDF, dataUrl: string, imgPxW: number, imgPxH: number, isFirst: boolean): void {
  if (!isFirst) {
    pdf.addPage()
  }

  const pageW = pdf.internal.pageSize.getWidth()
  const pageH = pdf.internal.pageSize.getHeight()
  const imgHmm = (imgPxH / imgPxW) * pageW
  const drawH = Math.min(imgHmm, pageH)
  const drawW = (drawH / imgHmm) * pageW

  pdf.addImage(dataUrl, 'PNG', 0, 0, drawW, drawH, undefined, 'FAST')
}

async function captureHtmlToPdf(html: string, filename: string): Promise<void> {
  const frame = document.createElement('iframe')
  frame.style.cssText =
    'position:fixed;left:-9999px;top:0;width:820px;height:2400px;border:0;opacity:0;pointer-events:none'
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
    prepareCaptureRoot(doc)

    const sheets = Array.from(doc.querySelectorAll('.sheet')) as HTMLElement[]
    if (!sheets.length) {
      throw new Error('لم يتم العثور على محتوى المستند')
    }

    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4', compress: true })

    for (let i = 0; i < sheets.length; i += 1) {
      const shot = await captureElementPng(sheets[i])
      addPageImage(pdf, shot.dataUrl, shot.width, shot.height, i === 0)
    }

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
