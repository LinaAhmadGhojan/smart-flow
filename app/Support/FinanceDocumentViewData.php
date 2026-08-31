<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\ProjectPayment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinanceDocumentViewData
{
    /** @return array<string, mixed> */
    public static function forInvoice(Invoice $invoice): array
    {
        $invoice->load(['quotation.items.product']);
        $quotation = $invoice->quotation;
        $company = CompanySettings::read();
        $logoPath = FinancePdfBranding::absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');
        $signaturePath = FinancePdfBranding::absoluteAssetPath($company['signature'] ?? null);
        $date = Carbon::parse($invoice->date);
        $currency = $invoice->currency ?: ($quotation->currency ?? 'AED');
        $discounts = self::discountsForInvoice($invoice, $quotation);
        $globalShareById = self::globalShareMap($quotation, $discounts);
        $items = self::mapItems($quotation?->items ?? collect(), $currency, $globalShareById, $quotation?->globalDiscountLabelShort() ?? '');

        $taxPercent = $quotation ? (float) $quotation->tax_percent : (float) $invoice->tax_percent;
        $taxAmount = $quotation ? (float) $quotation->tax_amount : (float) $invoice->tax_amount;
        $withholdingPercent = $quotation ? (float) $quotation->withholding_tax_percent : 0;
        $withholdingAmount = $quotation ? (float) $quotation->withholding_tax_amount : 0;
        $grandTotal = $quotation ? (float) $quotation->total : (float) $invoice->total;
        $paid = self::invoicePaidAmount($invoice, $grandTotal);
        $balanceDue = max(0, $grandTotal - $paid);

        return array_merge(FinancePdfBranding::companyViewData($company, $logoPath, $signaturePath), [
            'docType' => 'invoice',
            'docTitleAr' => 'فاتورة',
            'docTitleEn' => 'INVOICE',
            'numberLabelAr' => 'رقم الفاتورة',
            'docNumber' => $invoice->number,
            'dateLabel' => $date->format('Y / m / d'),
            'extraMetaRows' => [
                ['label' => 'Terms / الشروط', 'value' => 'NET 0'],
                ['label' => 'Due Date / الاستحقاق', 'value' => $date->format('Y / m / d')],
            ],
            'clientLabelAr' => 'فاتورة إلى',
            'clientName' => (string) $invoice->client_name,
            'clientNameIsArabic' => self::isArabic($invoice->client_name),
            'notes' => (string) ($invoice->notes ?: ''),
            'notesIsArabic' => self::isArabic($invoice->notes),
            'currency' => $currency,
            'items' => $items,
            'discounts' => $discounts,
            'taxPercent' => $taxPercent,
            'taxAmount' => $taxAmount,
            'withholdingPercent' => $withholdingPercent,
            'withholdingAmount' => $withholdingAmount,
            'grandTotal' => $balanceDue,
            'grandLabel' => 'Balance Due',
            'paid' => $paid,
            'showPaid' => true,
        ]);
    }

    /** @return array<string, mixed> */
    public static function forQuotation(Quotation $quotation): array
    {
        $quotation->load(['items.product']);
        $company = CompanySettings::read();
        $logoPath = FinancePdfBranding::absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');
        $signaturePath = FinancePdfBranding::absoluteAssetPath($company['signature'] ?? null);
        $discounts = $quotation->discountBreakdown();
        $globalShareById = self::globalShareMap($quotation, $discounts);
        $currency = $quotation->currency ?? 'AED';
        $items = self::mapItems($quotation->items, $currency, $globalShareById, $quotation->globalDiscountLabelShort());

        return array_merge(FinancePdfBranding::companyViewData($company, $logoPath, $signaturePath), [
            'docType' => 'quotation',
            'docTitleAr' => 'عرض أسعار',
            'docTitleEn' => 'ESTIMATE',
            'numberLabelAr' => 'رقم العرض',
            'docNumber' => $quotation->number,
            'dateLabel' => Carbon::parse($quotation->date)->format('Y / m / d'),
            'extraMetaRows' => [],
            'clientLabelAr' => 'عرض إلى',
            'clientName' => (string) $quotation->client_name,
            'clientNameIsArabic' => self::isArabic($quotation->client_name),
            'notes' => (string) ($quotation->comments ?: ''),
            'notesIsArabic' => self::isArabic($quotation->comments),
            'currency' => $currency,
            'items' => $items,
            'discounts' => $discounts,
            'taxPercent' => (float) $quotation->tax_percent,
            'taxAmount' => (float) $quotation->tax_amount,
            'withholdingPercent' => (float) $quotation->withholding_tax_percent,
            'withholdingAmount' => (float) $quotation->withholding_tax_amount,
            'grandTotal' => (float) $quotation->total,
            'grandLabel' => 'Total',
            'showPaid' => false,
        ]);
    }

    /** @return array<string, float> */
    private static function discountsForInvoice(Invoice $invoice, ?Quotation $quotation): array
    {
        if ($quotation) {
            return $quotation->discountBreakdown();
        }

        $partsSubtotal = (float) $invoice->amount;

        return [
            'gross_subtotal' => $partsSubtotal,
            'line_discount_total' => 0,
            'subtotal' => $partsSubtotal,
            'global_discount' => 0,
            'net_before_tax' => $partsSubtotal,
        ];
    }

    /** @return array<int, float> */
    private static function globalShareMap(?Quotation $quotation, array $discounts): array
    {
        if (!$quotation) {
            return [];
        }

        $productItems = $quotation->items->where('is_section', false)->values();
        $globalShares = Quotation::allocateGlobalDiscount(
            (float) $discounts['global_discount'],
            $productItems->map(fn (QuotationItem $i) => (float) $i->amount)->all()
        );
        $map = [];
        foreach ($productItems as $idx => $productItem) {
            $map[$productItem->id] = $globalShares[$idx] ?? 0.0;
        }

        return $map;
    }

    /** @return Collection<int, array<string, mixed>> */
    private static function mapItems(
        Collection $rows,
        string $currency,
        array $globalShareById,
        string $globalPctLabel
    ): Collection {
        return $rows->map(function (QuotationItem $item) use ($currency, $globalShareById, $globalPctLabel) {
            $imagePath = $item->is_section ? null : FinancePdfBranding::absoluteAssetPath($item->product?->image);
            $globalShare = $item->is_section ? 0.0 : (float) ($globalShareById[$item->id] ?? 0);
            $finalAmount = max(0, round((float) $item->amount - $globalShare, 2));
            $description = ProductDescription::withoutFeaturesSection($item->description);

            return [
                'is_section' => (bool) $item->is_section,
                'code' => $item->code,
                'description' => $description,
                'descriptionIsArabic' => self::isArabic($description),
                'quantity' => $item->quantity,
                'rate' => $item->rate,
                'final_amount' => $finalAmount,
                'amount' => $item->amount,
                'imageDataUri' => FinancePdfBranding::toDataUri($imagePath, 320),
            ];
        });
    }

    private static function isArabic(?string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', (string) $text);
    }

    private static function invoicePaidAmount(Invoice $invoice, float $grand): float
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
