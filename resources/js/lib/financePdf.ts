import { fetchAdminHtml } from '@/lib/api'
import { toPng } from 'html-to-image'
import { jsPDF } from 'jspdf'

/** A4 CSS px @ 96dpi — matches finance HTML sheet */
const DOC_W = 794
const PAGE_H = 1123
const FOOTER_H = 72
const PAD_X = 36
const PAD_TOP = 12
/** Normal pages: small bottom gap (no footer) */
const PAD_BOTTOM_NORMAL = 24
/** Last page only: reserve space for footer (keep tight so content+footer stay together) */
const PAD_BOTTOM_FOOTER = FOOTER_H + 8
const PIXEL_RATIO = 2

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

async function waitForImages(root: ParentNode): Promise<void> {
  const imgs = Array.from((root as Document | Element).querySelectorAll?.('img') ?? [])
  const pending = imgs.filter((img) => !(img as HTMLImageElement).complete)
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

function cloneEl(el: Element): HTMLElement {
  return el.cloneNode(true) as HTMLElement
}

function makePage(doc: Document, bottomPad: number): HTMLElement {
  const page = doc.createElement('div')
  page.className = 'sheet fd-print-page'
  page.style.cssText = [
    `width:${DOC_W}px`,
    `height:${PAGE_H}px`,
    `min-height:${PAGE_H}px`,
    `max-height:${PAGE_H}px`,
    'box-sizing:border-box',
    `padding:${PAD_TOP}px ${PAD_X}px ${bottomPad}px`,
    'background:#ffffff',
    'position:relative',
    'overflow:hidden',
    'margin:0',
  ].join(';')
  page.dataset.bottomPad = String(bottomPad)
  return page
}

/** Measure real content bottom (ignores overflow:hidden clipping). */
function contentBottom(page: HTMLElement): number {
  let max = 0
  for (const child of Array.from(page.children)) {
    const el = child as HTMLElement
    if (el.classList.contains('footer') || el.classList.contains('watermark')) continue
    const bottom = el.offsetTop + el.offsetHeight
    if (bottom > max) max = bottom
  }
  return max
}

function isMovablePageChild(el: HTMLElement): boolean {
  if (el.classList.contains('watermark') || el.classList.contains('footer')) return false
  if (el.tagName === 'TABLE' && el.classList.contains('header')) return false
  return true
}

function pageOverflows(page: HTMLElement, bottomPad?: number): boolean {
  const pad = bottomPad ?? Number(page.dataset.bottomPad || PAD_BOTTOM_NORMAL)
  const limit = PAGE_H - PAD_TOP - pad
  return contentBottom(page) > limit + 1
}

function attachFooter(page: HTMLElement, footerSrc: HTMLElement): void {
  page.style.paddingBottom = `${PAD_BOTTOM_FOOTER}px`
  page.dataset.bottomPad = String(PAD_BOTTOM_FOOTER)
  // Remove any previous footer clone
  page.querySelectorAll(':scope > .footer').forEach((f) => f.remove())
  const footer = cloneEl(footerSrc)
  footer.style.cssText = [
    'position:absolute',
    'left:0',
    'right:0',
    'bottom:0',
    `height:${FOOTER_H}px`,
    'overflow:hidden',
    'margin:0',
    'visibility:visible',
    'display:block',
  ].join(';')
  page.appendChild(footer)
}

function finalizeLastPageFooter(
  pages: HTMLElement[],
  doc: Document,
  footerSrc: HTMLElement | null,
  watermarkSrc: Element | null,
): void {
  if (!pages.length || !footerSrc) return

  const last = pages[pages.length - 1]
  last.querySelectorAll(':scope > .footer').forEach((f) => f.remove())
  last.style.paddingBottom = `${PAD_BOTTOM_FOOTER}px`
  last.dataset.bottomPad = String(PAD_BOTTOM_FOOTER)

  // Content already leaves room for thank-you footer → keep them on same page
  if (!pageOverflows(last, PAD_BOTTOM_FOOTER)) {
    attachFooter(last, footerSrc)
    return
  }

  // Free footer band by moving trailing blocks WITH the footer to a new last page.
  // Never create a blank page that only has the footer.
  const moved: HTMLElement[] = []
  while (pageOverflows(last, PAD_BOTTOM_FOOTER) && last.children.length > 0) {
    const kids = Array.from(last.children) as HTMLElement[]
    let victim: HTMLElement | null = null
    for (let i = kids.length - 1; i >= 0; i -= 1) {
      if (isMovablePageChild(kids[i])) {
        victim = kids[i]
        break
      }
    }
    if (!victim) break
    last.removeChild(victim)
    moved.unshift(victim)
  }

  if (!moved.length) {
    // Nothing movable — keep footer on this page (slight overlap better than empty page)
    attachFooter(last, footerSrc)
    return
  }

  // Previous page continues without footer
  last.style.paddingBottom = `${PAD_BOTTOM_NORMAL}px`
  last.dataset.bottomPad = String(PAD_BOTTOM_NORMAL)

  const host = last.parentElement
  const next = makePage(doc, PAD_BOTTOM_FOOTER)
  if (host) host.appendChild(next)
  pages.push(next)
  if (watermarkSrc) next.appendChild(cloneEl(watermarkSrc))
  for (const el of moved) next.appendChild(el)

  // Recurse in case moved blocks still need another split
  finalizeLastPageFooter(pages, doc, footerSrc, watermarkSrc)
}

function buildItemsPages(
  sheet: HTMLElement,
  doc: Document,
  watermarkSrc: Element | null,
): HTMLElement[] {
  const header = sheet.querySelector('table.header')
  const clientBar = sheet.querySelector('.client-for-bar')
  const itemsTable = sheet.querySelector('table.items')

  const host = doc.createElement('div')
  host.style.cssText =
    'position:fixed;left:-16000px;top:0;width:820px;background:#fff;opacity:0;pointer-events:none;'
  doc.body.appendChild(host)

  if (!itemsTable) {
    const page = makePage(doc, PAD_BOTTOM_NORMAL)
    host.appendChild(page)
    if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc))
    if (header) page.appendChild(cloneEl(header))
    return [page]
  }

  const pages: HTMLElement[] = []
  let page = makePage(doc, PAD_BOTTOM_NORMAL)
  host.appendChild(page)
  pages.push(page)
  let isFirstPage = true

  const thead = itemsTable.querySelector('thead')
  const colgroup = itemsTable.querySelector('colgroup')
  const rows = Array.from(itemsTable.querySelectorAll('tbody > tr'))

  const mountTable = (): HTMLTableSectionElement => {
    const table = doc.createElement('table')
    table.className = itemsTable.className
    table.style.width = '100%'
    table.style.borderCollapse = 'collapse'
    table.style.tableLayout = 'fixed'
    table.style.marginTop = isFirstPage ? '6px' : '0'
    if (colgroup) table.appendChild(cloneEl(colgroup))
    if (thead) table.appendChild(cloneEl(thead))
    const tbody = doc.createElement('tbody')
    table.appendChild(tbody)
    page.appendChild(table)
    return tbody
  }

  /** Continuation pages: table column header only (no company doc header). */
  const startContinuationPage = () => {
    isFirstPage = false
    page = makePage(doc, PAD_BOTTOM_NORMAL)
    host.appendChild(page)
    pages.push(page)
    if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc))
  }

  // Page 1: full document header + client + items
  if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc))
  if (header) page.appendChild(cloneEl(header))
  if (clientBar) page.appendChild(cloneEl(clientBar))

  let tbody = mountTable()
  if (pageOverflows(page)) {
    const tableEl = tbody.parentElement
    if (tableEl) page.removeChild(tableEl)
    // Extreme case: header alone fills page — continue with table header only
    startContinuationPage()
    tbody = mountTable()
  }

  for (const row of rows) {
    const tr = cloneEl(row)
    tbody.appendChild(tr)
    if (!pageOverflows(page)) continue

    tbody.removeChild(tr)

    // Keep section titles with the first product under them (no orphan heading at page bottom)
    const carry: HTMLElement[] = []
    while (tbody.lastElementChild?.classList.contains('section-row')) {
      carry.unshift(tbody.removeChild(tbody.lastElementChild) as HTMLElement)
    }

    // Drop empty items table left behind after moving the orphan section
    if (!tbody.children.length) {
      const emptyTable = tbody.parentElement
      if (emptyTable?.parentElement === page) page.removeChild(emptyTable)
    }

    startContinuationPage()
    tbody = mountTable()
    for (const section of carry) tbody.appendChild(section)
    tbody.appendChild(tr)
  }

  return pages
}

/**
 * Append summary after products:
 * - Totals table stays WHOLE (never split). Fits under products if space, else new page.
 * - Notes/comments fill remaining space when available.
 */
function appendSummaryToPages(
  pages: HTMLElement[],
  summarySheet: HTMLElement,
  doc: Document,
  watermarkSrc: Element | null,
): void {
  const summary = summarySheet.querySelector('.summary-content')
  if (!summary || !pages.length) return

  const host = pages[0].parentElement || doc.body
  let page = pages[pages.length - 1]

  const startPage = () => {
    page = makePage(doc, PAD_BOTTOM_NORMAL)
    host.appendChild(page)
    pages.push(page)
    if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc))
  }

  /** Place an atomic block: whole element on current page or next — never split. */
  const placeAtomic = (el: HTMLElement) => {
    const clone = cloneEl(el)
    page.appendChild(clone)
    if (!pageOverflows(page)) return
    page.removeChild(clone)
    startPage()
    page.appendChild(clone)
  }

  const placeComments = (headingSrc: HTMLElement, commentsSrc: HTMLElement) => {
    const heading = cloneEl(headingSrc)
    page.appendChild(heading)

    const parts = (
      commentsSrc.children.length
        ? Array.from(commentsSrc.children)
        : Array.from(commentsSrc.querySelectorAll('p, li'))
    ) as HTMLElement[]

    if (!parts.length) {
      const c = cloneEl(commentsSrc)
      page.appendChild(c)
      if (pageOverflows(page)) {
        page.removeChild(c)
        // Keep heading if possible; body alone on next page with heading copy
        if (pageOverflows(page)) {
          page.removeChild(heading)
          startPage()
          page.appendChild(cloneEl(headingSrc))
        } else {
          startPage()
          page.appendChild(cloneEl(headingSrc))
        }
        page.appendChild(c)
      }
      return
    }

    let bucket = doc.createElement('div')
    bucket.className = commentsSrc.className
    page.appendChild(bucket)

    for (const part of parts) {
      const piece = cloneEl(part)
      bucket.appendChild(piece)
      if (!pageOverflows(page)) continue

      bucket.removeChild(piece)

      // Nothing fit under heading on this page — try heading+first piece together
      if (bucket.childNodes.length === 0) {
        page.removeChild(bucket)
        const probe = doc.createElement('div')
        probe.className = commentsSrc.className
        probe.appendChild(piece)
        page.appendChild(probe)
        if (!pageOverflows(page)) {
          bucket = probe
          continue
        }
        // Drop heading from cramped page and start fresh with notes
        page.removeChild(probe)
        if (heading.parentElement === page) page.removeChild(heading)
        startPage()
        page.appendChild(cloneEl(headingSrc))
        bucket = doc.createElement('div')
        bucket.className = commentsSrc.className
        page.appendChild(bucket)
        bucket.appendChild(piece)
        continue
      }

      // Continue notes on next page (no need to repeat heading)
      startPage()
      bucket = doc.createElement('div')
      bucket.className = commentsSrc.className
      page.appendChild(bucket)
      bucket.appendChild(piece)
    }
  }

  const blocks = Array.from(summary.children) as HTMLElement[]
  for (let i = 0; i < blocks.length; i += 1) {
    const block = blocks[i]

    // Pricing / totals: never fragment
    if (block.classList.contains('totals-wrap') || block.classList.contains('totals')) {
      placeAtomic(block)
      continue
    }

    if (block.classList.contains('comments-h') && blocks[i + 1]?.classList.contains('comments')) {
      placeComments(block, blocks[i + 1])
      i += 1
      continue
    }

    // Signatures / company line: keep whole
    placeAtomic(block)
  }
}

async function capturePagePng(page: HTMLElement): Promise<string> {
  return toPng(page, {
    width: DOC_W,
    height: PAGE_H,
    canvasWidth: DOC_W * PIXEL_RATIO,
    canvasHeight: PAGE_H * PIXEL_RATIO,
    pixelRatio: PIXEL_RATIO,
    cacheBust: true,
    backgroundColor: '#ffffff',
    skipAutoScale: true,
  })
}

async function captureHtmlToPdf(html: string, filename: string): Promise<void> {
  const frame = document.createElement('iframe')
  frame.style.cssText =
    'position:fixed;left:-18000px;top:0;width:820px;height:2600px;border:0;opacity:0;pointer-events:none'
  frame.srcdoc = html
  document.body.appendChild(frame)

  try {
    await new Promise<void>((resolve) => {
      frame.addEventListener('load', () => resolve(), { once: true })
      setTimeout(() => resolve(), 2800)
    })

    const doc = frame.contentDocument
    if (!doc?.body) throw new Error('تعذر تحميل المستند')

    if (doc.fonts?.ready) await doc.fonts.ready
    await waitForImages(doc)
    await new Promise((r) => setTimeout(r, 400))

    doc.body.classList.add('fd-capture')
    const wrap = doc.querySelector('.page-wrap') as HTMLElement | null
    if (wrap) {
      wrap.style.background = '#fff'
      wrap.style.padding = '0'
      wrap.style.display = 'block'
      wrap.style.minHeight = 'auto'
    }

    const sheets = Array.from(doc.querySelectorAll('.sheet')) as HTMLElement[]
    if (!sheets.length) throw new Error('لم يتم العثور على محتوى المستند')

    const footerSrc = doc.querySelector('.footer') as HTMLElement | null
    const watermarkSrc = doc.querySelector('.watermark')

    const itemsSheet = sheets.find((s) => !s.classList.contains('sheet-summary'))
    const summarySheet = sheets.find((s) => s.classList.contains('sheet-summary'))

    for (const sheet of sheets) sheet.style.display = 'none'

    const printPages: HTMLElement[] = itemsSheet
      ? buildItemsPages(itemsSheet, doc, watermarkSrc)
      : []

    if (summarySheet) {
      if (!printPages.length) {
        // No items — start a blank page then append summary
        const host = doc.createElement('div')
        host.style.cssText =
          'position:fixed;left:-16000px;top:0;width:820px;background:#fff;opacity:0;pointer-events:none;'
        doc.body.appendChild(host)
        const page = makePage(doc, PAD_BOTTOM_NORMAL)
        host.appendChild(page)
        if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc))
        printPages.push(page)
      }
      appendSummaryToPages(printPages, summarySheet, doc, watermarkSrc)
    }

    if (!printPages.length) throw new Error('تعذر تقسيم صفحات المستند')

    // Footer + «شكراً لتعاملكم معنا» on the LAST page only
    finalizeLastPageFooter(printPages, doc, footerSrc, watermarkSrc)

    await waitForImages(doc)
    await new Promise((r) => setTimeout(r, 250))

    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4', compress: true })
    const pageW = pdf.internal.pageSize.getWidth()
    const pageHmm = pdf.internal.pageSize.getHeight()

    for (let i = 0; i < printPages.length; i += 1) {
      if (i > 0) pdf.addPage()
      const dataUrl = await capturePagePng(printPages[i])
      pdf.addImage(dataUrl, 'PNG', 0, 0, pageW, pageHmm, undefined, 'FAST')
    }

    pdf.save(filename)
  } finally {
    frame.remove()
  }
}

export async function exportInvoicePdf(invoiceId: number | string, number?: string): Promise<void> {
  const html = await fetchAdminHtml(invoiceHtmlPath(invoiceId))
  await captureHtmlToPdf(html, invoicePdfFilename(number))
}

export async function exportQuotationPdf(quotationId: number | string, number?: string): Promise<void> {
  const html = await fetchAdminHtml(quotationHtmlPath(quotationId))
  await captureHtmlToPdf(html, quotationPdfFilename(number))
}
