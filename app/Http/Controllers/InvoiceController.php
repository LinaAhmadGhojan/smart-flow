<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ProjectPayment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Support\ArabicPdfText;
use App\Support\CompanySettings;
use App\Support\DompdfFontCache;
use App\Support\ProductDescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class InvoiceController extends Controller
{
    public function index()
    {
        return response()->json(
            Invoice::with(['quotation:id,number,total,customer_id', 'project:id,title,title_ar,customer_id'])
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
        );
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'quotation.items.product',
            'quotation.project:id,title,title_ar',
            'project:id,title,title_ar,customer_id,location',
            'payments' => fn ($q) => $q->orderByDesc('paid_at')->orderByDesc('id'),
        ]);

        $quotation = $invoice->quotation;
        $grand = $quotation ? (float) $quotation->total : (float) ($invoice->total ?: $invoice->amount);
        $paid = $this->invoicePaidAmount($invoice, $grand);

        $data = $invoice->toArray();
        $data['payment_summary'] = [
            'paid' => round($paid, 2),
            'balance_due' => round(max(0, $grand - $paid), 2),
            'grand_total' => round($grand, 2),
        ];

        return response()->json($data);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'date' => 'sometimes|date',
            'client_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:draft,sent,paid,cancelled',
            'notes' => 'nullable|string|max:5000',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $invoice->update($validated);

        return response()->json($invoice->fresh(['quotation', 'project']));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function pdf(Invoice $invoice)
    {
        DompdfFontCache::ensureReady();
        try {
            $invoice->load(['quotation.items.product']);
            $quotation = $invoice->quotation;
            $company = $this->companySettings();
            $logoPath = $this->absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');
            $signaturePath = $this->absoluteAssetPath($company['signature'] ?? null);
            $date = Carbon::parse($invoice->date);
            $currency = $invoice->currency ?: ($quotation->currency ?? 'AED');

            $partsSubtotal = $quotation ? (float) $quotation->subtotal : (float) $invoice->amount;
            $discounts = $quotation ? $quotation->discountBreakdown() : [
                'gross_subtotal' => $partsSubtotal,
                'line_discount_total' => 0,
                'subtotal' => $partsSubtotal,
                'global_discount' => 0,
                'net_before_tax' => $partsSubtotal,
            ];
            $globalPctLabel = $quotation?->globalDiscountLabelShort() ?? '';
            $globalShareById = [];
            if ($quotation) {
                $productItems = $quotation->items->where('is_section', false)->values();
                $globalShares = Quotation::allocateGlobalDiscount(
                    (float) $discounts['global_discount'],
                    $productItems->map(fn (QuotationItem $i) => (float) $i->amount)->all()
                );
                foreach ($productItems as $idx => $productItem) {
                    $globalShareById[$productItem->id] = $globalShares[$idx] ?? 0.0;
                }
            }

            $items = collect();
            if ($quotation) {
                $items = $quotation->items->map(function (QuotationItem $item) use ($currency, $globalShareById, $globalPctLabel) {
                    $imagePath = $item->is_section ? null : $this->absoluteAssetPath($item->product?->image);
                    $lineSubtotal = round((float) $item->quantity * (float) $item->rate, 2);
                    $globalShare = $item->is_section ? 0.0 : (float) ($globalShareById[$item->id] ?? 0);
                    $globalDiscountLabel = '—';
                    if ($globalShare > 0) {
                        $globalDiscountLabel = ($globalPctLabel !== '' ? $globalPctLabel . ' → ' : '')
                            . '−' . $currency . ' ' . number_format($globalShare, 2);
                    }
                    $finalAmount = max(0, round((float) $item->amount - $globalShare, 2));

                    return [
                        'is_section' => (bool) $item->is_section,
                        'code' => $item->code,
                        'description' => ArabicPdfText::shape(ProductDescription::withoutFeaturesSection($item->description)),
                        'descriptionIsArabic' => (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $item->description),
                        'quantity' => $item->quantity,
                        'rate' => $item->rate,
                        'line_subtotal' => $lineSubtotal,
                        'discount_label' => $item->discountLabel($currency),
                        'discount_amount' => (float) ($item->discount_amount ?? 0),
                        'global_discount_label' => $globalDiscountLabel,
                        'global_discount_share' => $globalShare,
                        'amount' => $item->amount,
                        'final_amount' => $finalAmount,
                        'imageDataUri' => $this->toDataUri($imagePath, 270),
                    ];
                });
            }

            $taxPercent = $quotation ? (float) $quotation->tax_percent : (float) $invoice->tax_percent;
            $taxAmount = $quotation ? (float) $quotation->tax_amount : (float) $invoice->tax_amount;
            $withholdingPercent = $quotation ? (float) $quotation->withholding_tax_percent : 0;
            $withholdingAmount = $quotation ? (float) $quotation->withholding_tax_amount : 0;
            $grandTotal = $quotation ? (float) $quotation->total : (float) $invoice->total;

            $paid = $this->invoicePaidAmount($invoice, $grandTotal);
            $balanceDue = max(0, $grandTotal - $paid);

            $pdf = Pdf::loadView('invoices.pdf', [
                'invoice' => $invoice,
                'quotation' => $quotation,
                'currency' => $currency,
                'dateLabel' => $date->format('d/m/Y'),
                'dueDateLabel' => $date->format('d/m/Y'),
                'terms' => 'NET 0',
                'company' => $company,
                'companyLegalName' => $company['legalName']
                    ?? 'AL TDFUQ AL DHAKI ELECTRICITY TRANSMISSION & CONTROL EQUIPMENT INSTALLATION WORKS L.L.C',
                'companyCountry' => $company['contact']['address']['en']
                    ?? ($company['seo']['location']['country'] ?? 'United Arab Emirates'),
                'trn' => trim((string) ($company['trn'] ?? ($company['taxNumber'] ?? ''))),
                'logoDataUri' => $this->toDataUri($logoPath, 160),
                'signatureDataUri' => $this->toDataUri($signaturePath, 420),
                'clientName' => ArabicPdfText::shape($invoice->client_name),
                'clientNameIsArabic' => (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $invoice->client_name),
                'notes' => ArabicPdfText::shape($invoice->notes ?: ''),
                'notesIsArabic' => (bool) preg_match('/[\x{0600}-\x{06FF}]/u', (string) $invoice->notes),
                'arabicFontUrl' => DompdfFontCache::arabicFontUrl(),
                'items' => $items,
                'partsSubtotal' => $discounts['subtotal'],
                'discounts' => $discounts,
                'globalPctLabel' => $globalPctLabel,
                'lineDiscountTotal' => $discounts['line_discount_total'],
                'globalDiscount' => $discounts['global_discount'],
                'discount' => $discounts['line_discount_total'] + $discounts['global_discount'],
                'taxPercent' => $taxPercent,
                'taxAmount' => $taxAmount,
                'withholdingPercent' => $withholdingPercent,
                'withholdingAmount' => $withholdingAmount,
                'grandTotal' => $grandTotal,
                'paid' => $paid,
                'balanceDue' => $balanceDue,
            ])->setPaper('a4');

            return $pdf->download($invoice->number . '.pdf');
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'تعذر تصدير PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function companySettings(): array
    {
        return CompanySettings::read();
    }

    private function absoluteAssetPath(?string $webPath): ?string
    {
        if (!$webPath) {
            return null;
        }

        $webPath = preg_replace('#^/public/#', '/', $webPath) ?? $webPath;
        $relative = ltrim($webPath, '/');
        $candidates = [
            public_path($relative),
            public_path(str_replace('storage/', 'storage/', $relative)),
            storage_path('app/public/' . ltrim(str_replace(['/storage/', 'storage/'], '', $webPath), '/')),
        ];

        foreach (array_unique($candidates) as $absolute) {
            if ($absolute && File::exists($absolute) && is_file($absolute)) {
                return $absolute;
            }
        }

        return null;
    }

    private function toDataUri(?string $absolutePath, int $maxSide = 0): ?string
    {
        if (!$absolutePath || !File::exists($absolutePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $src = false;

        if (function_exists('imagecreatefromstring')) {
            $raw = @File::get($absolutePath);
            $src = $raw ? @imagecreatefromstring($raw) : false;
        }
        if ($src === false && $ext === 'webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($absolutePath);
        }
        if ($src === false && $ext === 'png' && function_exists('imagecreatefrompng')) {
            $src = @imagecreatefrompng($absolutePath);
        }
        if ($src === false && in_array($ext, ['jpg', 'jpeg'], true) && function_exists('imagecreatefromjpeg')) {
            $src = @imagecreatefromjpeg($absolutePath);
        }

        if ($src !== false) {
            $w = imagesx($src);
            $h = imagesy($src);
            if ($maxSide > 0 && max($w, $h) < (int) ($maxSide * 0.75)) {
                $scale = $maxSide / max($w, $h);
            } elseif ($maxSide > 0 && ($w > $maxSide || $h > $maxSide)) {
                $scale = min($maxSide / $w, $maxSide / $h);
            } else {
                $scale = 1;
            }
            $nw = max(1, (int) round($w * $scale));
            $nh = max(1, (int) round($h * $scale));
            $dst = imagecreatetruecolor($nw, $nh);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            imagealphablending($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);

            ob_start();
            // DomPDF is most reliable with JPEG for item thumbs
            imagejpeg($dst, null, 90);
            $bin = ob_get_clean();
            imagedestroy($dst);
            if ($bin) {
                return 'data:image/jpeg;base64,' . base64_encode($bin);
            }
        }

        if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';

            return 'data:' . $mime . ';base64,' . base64_encode(File::get($absolutePath));
        }

        return null;
    }

    private function invoicePaidAmount(Invoice $invoice, float $grand): float
    {
        $invoicePaid = (float) ProjectPayment::where('invoice_id', $invoice->id)->sum('amount');
        if ($invoicePaid > 0) {
            return $invoicePaid;
        }

        if ($invoice->project_id) {
            return (float) ProjectPayment::where('project_id', $invoice->project_id)
                ->whereNull('invoice_id')
                ->sum('amount');
        }

        if ($invoice->status === 'paid') {
            return $grand;
        }

        return 0.0;
    }
}
