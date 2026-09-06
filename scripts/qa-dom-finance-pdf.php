<?php

/**
 * QA: paginate finance HTML like resources/js/lib/financePdf.ts
 * (footer on LAST page only), screenshot each A4 page.
 *
 * Usage: php scripts/qa-dom-finance-pdf.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\Quotation;
use App\Support\BrowserPdf;
use App\Support\ChromeDevtools;
use App\Support\FinanceDocumentViewData;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

$outDir = storage_path('app/pdf-tmp/qa-dom');
File::ensureDirectoryExists($outDir);

$DOC_W = 794;
$PAGE_H = 1123;

$paginateJs = <<<'JS'
(() => {
  const DOC_W = 794;
  const PAGE_H = 1123;
  const FOOTER_H = 76;
  const PAD_X = 36;
  const PAD_TOP = 12;
  const PAD_BOTTOM_NORMAL = 24;
  const PAD_BOTTOM_FOOTER = FOOTER_H + 18;

  function cloneEl(el) { return el.cloneNode(true); }

  function makePage(doc, bottomPad) {
    const page = doc.createElement('div');
    page.className = 'sheet fd-print-page';
    page.style.cssText = [
      `width:${DOC_W}px`, `height:${PAGE_H}px`, `min-height:${PAGE_H}px`, `max-height:${PAGE_H}px`,
      'box-sizing:border-box',
      `padding:${PAD_TOP}px ${PAD_X}px ${bottomPad}px`,
      'background:#ffffff', 'position:relative', 'overflow:hidden', 'margin:0',
    ].join(';');
    page.dataset.bottomPad = String(bottomPad);
    return page;
  }

  function contentBottom(page) {
    let max = 0;
    for (const child of Array.from(page.children)) {
      if (child.classList.contains('footer')) continue;
      const bottom = child.offsetTop + child.offsetHeight;
      if (bottom > max) max = bottom;
    }
    return max;
  }

  function pageOverflows(page, bottomPad) {
    const pad = bottomPad != null ? bottomPad : Number(page.dataset.bottomPad || PAD_BOTTOM_NORMAL);
    const limit = PAGE_H - PAD_TOP - pad;
    return contentBottom(page) > limit + 1;
  }

  function attachFooter(page, footerSrc) {
    page.style.paddingBottom = `${PAD_BOTTOM_FOOTER}px`;
    page.dataset.bottomPad = String(PAD_BOTTOM_FOOTER);
    page.querySelectorAll(':scope > .footer').forEach((f) => f.remove());
    const footer = cloneEl(footerSrc);
    footer.style.cssText = [
      'position:absolute','left:0','right:0','bottom:0',
      `height:${FOOTER_H}px`,'overflow:hidden','margin:0',
      'visibility:visible','display:block',
    ].join(';');
    page.appendChild(footer);
  }

  function finalizeLastPageFooter(pages, doc, footerSrc, watermarkSrc) {
    if (!pages.length || !footerSrc) return;
    const last = pages[pages.length - 1];
    attachFooter(last, footerSrc);
    if (!pageOverflows(last, PAD_BOTTOM_FOOTER)) return;

    last.querySelectorAll(':scope > .footer').forEach((f) => f.remove());
    last.style.paddingBottom = `${PAD_BOTTOM_NORMAL}px`;
    last.dataset.bottomPad = String(PAD_BOTTOM_NORMAL);

    const moved = [];
    while (pageOverflows(last, PAD_BOTTOM_NORMAL) && last.children.length > 0) {
      const kids = Array.from(last.children);
      const victim = kids[kids.length - 1];
      if (!victim || victim.classList.contains('watermark') || (victim.tagName === 'TABLE' && victim.classList.contains('header'))) break;
      last.removeChild(victim);
      moved.unshift(victim);
    }

    const host = last.parentElement;
    const next = makePage(doc, PAD_BOTTOM_FOOTER);
    if (host) host.appendChild(next);
    pages.push(next);
    if (watermarkSrc) next.appendChild(cloneEl(watermarkSrc));
    for (const el of moved) next.appendChild(el);
    attachFooter(next, footerSrc);
  }

  function buildItemsPages(sheet, doc, watermarkSrc) {
    const header = sheet.querySelector('table.header');
    const clientBar = sheet.querySelector('.client-for-bar');
    const itemsTable = sheet.querySelector('table.items');
    const host = doc.createElement('div');
    host.style.cssText = 'position:fixed;left:0;top:0;width:820px;background:#fff;';
    doc.body.appendChild(host);

    if (!itemsTable) {
      const page = makePage(doc, PAD_BOTTOM_NORMAL);
      host.appendChild(page);
      if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc));
      if (header) page.appendChild(cloneEl(header));
      return [page];
    }

    const pages = [];
    let page = makePage(doc, PAD_BOTTOM_NORMAL);
    host.appendChild(page);
    pages.push(page);
    let isFirstPage = true;

    const thead = itemsTable.querySelector('thead');
    const colgroup = itemsTable.querySelector('colgroup');
    const rows = Array.from(itemsTable.querySelectorAll('tbody > tr'));

    const mountTable = () => {
      const table = doc.createElement('table');
      table.className = itemsTable.className;
      table.style.width = '100%';
      table.style.borderCollapse = 'collapse';
      table.style.tableLayout = 'fixed';
      table.style.marginTop = isFirstPage ? '6px' : '0';
      if (colgroup) table.appendChild(cloneEl(colgroup));
      if (thead) table.appendChild(cloneEl(thead));
      const tbody = doc.createElement('tbody');
      table.appendChild(tbody);
      page.appendChild(table);
      return tbody;
    };

    const startContinuationPage = () => {
      isFirstPage = false;
      page = makePage(doc, PAD_BOTTOM_NORMAL);
      host.appendChild(page);
      pages.push(page);
      if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc));
    };

    if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc));
    if (header) page.appendChild(cloneEl(header));
    if (clientBar) page.appendChild(cloneEl(clientBar));

    let tbody = mountTable();
    if (pageOverflows(page)) {
      const tableEl = tbody.parentElement;
      if (tableEl) page.removeChild(tableEl);
      startContinuationPage();
      tbody = mountTable();
    }

    for (const row of rows) {
      const tr = cloneEl(row);
      tbody.appendChild(tr);
      if (!pageOverflows(page)) continue;
      tbody.removeChild(tr);
      startContinuationPage();
      tbody = mountTable();
      tbody.appendChild(tr);
    }
    return pages;
  }

  function appendSummaryToPages(pages, summarySheet, doc, watermarkSrc) {
    const summary = summarySheet.querySelector('.summary-content');
    if (!summary || !pages.length) return;

    const host = pages[0].parentElement || doc.body;
    let page = pages[pages.length - 1];

    const startPage = () => {
      page = makePage(doc, PAD_BOTTOM_NORMAL);
      host.appendChild(page);
      pages.push(page);
      if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc));
    };

    const placeAtomic = (el) => {
      const clone = cloneEl(el);
      page.appendChild(clone);
      if (!pageOverflows(page)) return;
      page.removeChild(clone);
      startPage();
      page.appendChild(clone);
    };

    const placeComments = (headingSrc, commentsSrc) => {
      const heading = cloneEl(headingSrc);
      page.appendChild(heading);
      const parts = commentsSrc.children.length
        ? Array.from(commentsSrc.children)
        : Array.from(commentsSrc.querySelectorAll('p, li'));
      if (!parts.length) {
        const c = cloneEl(commentsSrc);
        page.appendChild(c);
        if (pageOverflows(page)) {
          page.removeChild(c);
          if (pageOverflows(page)) {
            page.removeChild(heading);
            startPage();
            page.appendChild(cloneEl(headingSrc));
          } else {
            startPage();
            page.appendChild(cloneEl(headingSrc));
          }
          page.appendChild(c);
        }
        return;
      }
      let bucket = doc.createElement('div');
      bucket.className = commentsSrc.className;
      page.appendChild(bucket);
      for (const part of parts) {
        const piece = cloneEl(part);
        bucket.appendChild(piece);
        if (!pageOverflows(page)) continue;
        bucket.removeChild(piece);
        if (bucket.childNodes.length === 0) {
          page.removeChild(bucket);
          const probe = doc.createElement('div');
          probe.className = commentsSrc.className;
          probe.appendChild(piece);
          page.appendChild(probe);
          if (!pageOverflows(page)) {
            bucket = probe;
            continue;
          }
          page.removeChild(probe);
          if (heading.parentElement === page) page.removeChild(heading);
          startPage();
          page.appendChild(cloneEl(headingSrc));
          bucket = doc.createElement('div');
          bucket.className = commentsSrc.className;
          page.appendChild(bucket);
          bucket.appendChild(piece);
          continue;
        }
        startPage();
        bucket = doc.createElement('div');
        bucket.className = commentsSrc.className;
        page.appendChild(bucket);
        bucket.appendChild(piece);
      }
    };

    const blocks = Array.from(summary.children);
    for (let i = 0; i < blocks.length; i++) {
      const block = blocks[i];
      if (block.classList.contains('totals-wrap') || block.classList.contains('totals')) {
        placeAtomic(block);
        continue;
      }
      if (block.classList.contains('comments-h') && blocks[i + 1]?.classList.contains('comments')) {
        placeComments(block, blocks[i + 1]);
        i += 1;
        continue;
      }
      placeAtomic(block);
    }
  }

  document.body.classList.add('fd-capture');
  const wrap = document.querySelector('.page-wrap');
  if (wrap) {
    wrap.style.background = '#fff';
    wrap.style.padding = '0';
    wrap.style.display = 'block';
    wrap.style.minHeight = 'auto';
  }

  const sheets = Array.from(document.querySelectorAll('.sheet'));
  const footerSrc = document.querySelector('.footer');
  const watermarkSrc = document.querySelector('.watermark');
  const itemsSheet = sheets.find((s) => !s.classList.contains('sheet-summary'));
  const summarySheet = sheets.find((s) => s.classList.contains('sheet-summary'));
  sheets.forEach((s) => { s.style.display = 'none'; });

  const printPages = itemsSheet ? buildItemsPages(itemsSheet, document, watermarkSrc) : [];
  if (summarySheet) {
    if (!printPages.length) {
      const host = document.createElement('div');
      host.style.cssText = 'position:fixed;left:0;top:0;width:820px;background:#fff;';
      document.body.appendChild(host);
      const page = makePage(document, PAD_BOTTOM_NORMAL);
      host.appendChild(page);
      if (watermarkSrc) page.appendChild(cloneEl(watermarkSrc));
      printPages.push(page);
    }
    appendSummaryToPages(printPages, summarySheet, document, watermarkSrc);
  }

  finalizeLastPageFooter(printPages, document, footerSrc, watermarkSrc);

  const stack = document.createElement('div');
  stack.id = 'fd-qa-stack';
  stack.style.cssText = 'position:absolute;left:0;top:0;background:#fff;';
  printPages.forEach((p, idx) => {
    p.style.position = 'relative';
    p.style.left = '0';
    p.style.top = '0';
    p.style.marginBottom = '0';
    p.dataset.qaIndex = String(idx);
    stack.appendChild(p);
  });
  document.body.innerHTML = '';
  document.body.style.margin = '0';
  document.body.style.background = '#fff';
  document.body.appendChild(stack);

  return printPages.length;
})()
JS;

function renderDomQa(string $label, array $data, string $stem, string $outDir, string $paginateJs, int $DOC_W, int $PAGE_H): void
{
    echo "QA {$label}...\n";
    $html = view('finance-documents.html', $data)->render();
    $htmlFile = $outDir . DIRECTORY_SEPARATOR . $stem . '.html';
    file_put_contents($htmlFile, $html);

    $ref = new ReflectionClass(BrowserPdf::class);
    $m = $ref->getMethod('binary');
    $m->setAccessible(true);
    $chrome = $m->invoke(null);
    if (!$chrome) {
        echo "  FAIL: Chrome not found\n";
        return;
    }

    $profileDir = $outDir . DIRECTORY_SEPARATOR . 'profile-' . $stem;
    File::ensureDirectoryExists($profileDir);

    $portMethod = $ref->getMethod('freePort');
    $portMethod->setAccessible(true);
    $port = $portMethod->invoke(null);

    $chromeProc = new Process([
        $chrome,
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        '--no-first-run',
        '--disable-extensions',
        '--hide-scrollbars',
        '--window-size=900,1400',
        '--allow-file-access-from-files',
        '--remote-allow-origins=*',
        '--remote-debugging-address=127.0.0.1',
        '--remote-debugging-port=' . $port,
        '--user-data-dir=' . $profileDir,
        'about:blank',
    ]);
    $chromeProc->setTimeout(90);
    $chromeProc->start();

    try {
        $waitPort = $ref->getMethod('waitForChromePort');
        $waitPort->setAccessible(true);
        $waitPort->invoke(null, $port, 12);

        $waitDbg = $ref->getMethod('waitForDebuggerUrl');
        $waitDbg->setAccessible(true);
        $wsUrl = $waitDbg->invoke(null, $port, 8);
        if (!$wsUrl) {
            echo "  FAIL: no debugger URL\n";
            return;
        }

        $fileUrlMethod = $ref->getMethod('fileUrl');
        $fileUrlMethod->setAccessible(true);
        $fileUrl = $fileUrlMethod->invoke(null, $htmlFile);

        $client = new ChromeDevtools($wsUrl);
        $client->call('Page.enable');
        $client->call('Runtime.enable');
        $client->call('Page.navigate', ['url' => $fileUrl]);

        $deadline = microtime(true) + 10;
        while (microtime(true) < $deadline) {
            $ready = $client->call('Runtime.evaluate', [
                'expression' => 'document.readyState',
                'returnByValue' => true,
            ]);
            $state = $ready['result']['result']['value'] ?? '';
            if ($state === 'complete' || $state === 'interactive') {
                break;
            }
            usleep(150000);
        }
        usleep(800000);
        try {
            $client->call('Runtime.evaluate', [
                'expression' => 'document.fonts && document.fonts.ready ? document.fonts.ready.then(() => 1) : 1',
                'awaitPromise' => true,
                'returnByValue' => true,
            ]);
        } catch (Throwable) {
        }

        $eval = $client->call('Runtime.evaluate', [
            'expression' => $paginateJs,
            'returnByValue' => true,
        ]);
        $pageCount = (int) ($eval['result']['result']['value'] ?? 0);
        if ($pageCount < 1) {
            echo "  FAIL: pagination returned {$pageCount}\n";
            echo '  detail: ' . json_encode($eval['result'] ?? []) . "\n";
            return;
        }
        echo "  pages: {$pageCount}\n";
        usleep(400000);

        for ($i = 0; $i < $pageCount; $i++) {
            $shot = $client->call('Page.captureScreenshot', [
                'format' => 'png',
                'fromSurface' => true,
                'clip' => [
                    'x' => 0,
                    'y' => $i * $PAGE_H,
                    'width' => $DOC_W,
                    'height' => $PAGE_H,
                    'scale' => 1,
                ],
                'captureBeyondViewport' => true,
            ]);
            $bytes = base64_decode($shot['result']['data'] ?? '', true);
            if ($bytes === false || strlen($bytes) < 500) {
                echo "  FAIL: screenshot page {$i}\n";
                continue;
            }
            $png = $outDir . DIRECTORY_SEPARATOR . $stem . '-p' . ($i + 1) . '.png';
            file_put_contents($png, $bytes);
            echo "  saved {$png}\n";
        }

        $printed = $client->call('Page.printToPDF', [
            'landscape' => false,
            'paperWidth' => 8.27,
            'paperHeight' => 11.69,
            'printBackground' => true,
            'preferCSSPageSize' => false,
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
        ]);
        $pdfBytes = base64_decode($printed['result']['data'] ?? '', true);
        if ($pdfBytes && strlen($pdfBytes) > 1000) {
            $pdfPath = $outDir . DIRECTORY_SEPARATOR . $stem . '.pdf';
            file_put_contents($pdfPath, $pdfBytes);
            echo "  PDF: {$pdfPath} (" . strlen($pdfBytes) . " bytes)\n";
        }

        $client->close();
    } finally {
        $chromeProc->stop(2);
        File::deleteDirectory($profileDir);
    }
}

$quotation = Quotation::query()->where('number', 'QUE-26-000002')->first()
    ?: Quotation::query()->orderByDesc('id')->first();

if (!$quotation) {
    echo "No quotation found.\n";
    exit(1);
}

echo "Quotation: {$quotation->number} (id={$quotation->id})\n";
renderDomQa(
    'quotation',
    FinanceDocumentViewData::forQuotation($quotation),
    $quotation->number,
    $outDir,
    $paginateJs,
    $DOC_W,
    $PAGE_H
);

$invoice = Invoice::query()->where('number', 'like', 'INV-%')->orderByDesc('id')->first();
if ($invoice) {
    echo "Invoice: {$invoice->number} (id={$invoice->id})\n";
    renderDomQa(
        'invoice',
        FinanceDocumentViewData::forInvoice($invoice),
        $invoice->number,
        $outDir,
        $paginateJs,
        $DOC_W,
        $PAGE_H
    );
} else {
    echo "No invoice found (skipped).\n";
}

echo "Done.\n";
