<?php

/**
 * Local QA: render quotation + invoice finance PDFs via BrowserPdf and save for visual check.
 * Usage: php scripts/qa-finance-pdf.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\Quotation;
use App\Support\BrowserPdf;
use App\Support\FinanceDocumentViewData;
use Illuminate\Support\Facades\File;

$outDir = storage_path('app/pdf-tmp/qa');
File::ensureDirectoryExists($outDir);

function renderDoc(string $label, array $data, string $outFile): void
{
    echo "Rendering {$label}...\n";
    $html = view('finance-documents.html', $data)->render();
    $pdf = BrowserPdf::render($html, 2000);
    if ($pdf === null) {
        echo "  FAIL: BrowserPdf unavailable\n";
        // Save HTML for manual inspection
        $htmlPath = preg_replace('/\.pdf$/', '.html', $outFile);
        file_put_contents($htmlPath, $html);
        echo "  Saved HTML fallback: {$htmlPath}\n";
        return;
    }
    file_put_contents($outFile, $pdf);
    echo "  OK: {$outFile} (" . strlen($pdf) . " bytes)\n";
}

$quotation = Quotation::query()->where('number', 'QUE-26-000002')->first()
    ?: Quotation::query()->orderByDesc('id')->first();

if (!$quotation) {
    echo "No quotation found.\n";
    exit(1);
}

echo "Quotation: {$quotation->number} (id={$quotation->id})\n";
renderDoc(
    'quotation',
    FinanceDocumentViewData::forQuotation($quotation),
    $outDir . DIRECTORY_SEPARATOR . $quotation->number . '-qa.pdf'
);

$invoice = Invoice::query()->where('number', 'like', 'INV-%')->orderByDesc('id')->first();
if ($invoice) {
    echo "Invoice: {$invoice->number} (id={$invoice->id})\n";
    renderDoc(
        'invoice',
        FinanceDocumentViewData::forInvoice($invoice),
        $outDir . DIRECTORY_SEPARATOR . $invoice->number . '-qa.pdf'
    );
} else {
    echo "No invoice found (skipped).\n";
}

echo "Done.\n";
