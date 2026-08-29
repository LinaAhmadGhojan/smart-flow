<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Support\ArabicPdfText;
use App\Support\CompanySettings;
use App\Support\DompdfFontCache;
use App\Support\ProductDescription;
use App\Support\StorageUrl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    public function index()
    {
        return response()->json(
            Quotation::with(['project:id,title,title_ar', 'customer:id,name'])
                ->withCount('items')
                ->withSum(['invoices as invoiced_sum' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                }], 'amount')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
                ->map(function (Quotation $q) {
                    $q->setAttribute('invoiced_amount', (float) ($q->invoiced_sum ?? 0));
                    $q->setAttribute('remaining_amount', max(0, (float) $q->total - (float) ($q->invoiced_sum ?? 0)));
                    return $q;
                })
        );
    }

    public function show(Quotation $quotation)
    {
        return response()->json($quotation->load(['items.product', 'invoices', 'customer', 'project']));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $quotation = DB::transaction(function () use ($data) {
            $quotation = Quotation::create([
                'number' => $data['number'] ?? $this->nextNumber('QUE'),
                'date' => $data['date'],
                'client_name' => $data['client_name'],
                'customer_id' => $data['customer_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'comments' => $data['comments'] ?? null,
                'currency' => $data['currency'] ?? 'AED',
                'tax_percent' => $data['tax_percent'] ?? 0,
                'withholding_tax_percent' => $data['withholding_tax_percent'] ?? 0,
                'discount_type' => $this->normalizeDiscountType($data['discount_type'] ?? null),
                'discount_value' => $this->normalizeDiscountValue($data['discount_type'] ?? null, $data['discount_value'] ?? null),
            ]);

            $this->syncItems($quotation, $data['items'] ?? []);
            $quotation->recalculateTotals();

            return $quotation->fresh(['items.product']);
        });

        return response()->json($quotation, 201);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $this->validated($request, $quotation->id);

        DB::transaction(function () use ($quotation, $data) {
            $quotation->update([
                'number' => $data['number'] ?? $quotation->number,
                'date' => $data['date'],
                'client_name' => $data['client_name'],
                'customer_id' => $data['customer_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'status' => $data['status'] ?? $quotation->status,
                'comments' => $data['comments'] ?? null,
                'currency' => $data['currency'] ?? 'AED',
                'tax_percent' => $data['tax_percent'] ?? 0,
                'withholding_tax_percent' => $data['withholding_tax_percent'] ?? 0,
                'discount_type' => $this->normalizeDiscountType($data['discount_type'] ?? null),
                'discount_value' => $this->normalizeDiscountValue($data['discount_type'] ?? null, $data['discount_value'] ?? null),
            ]);

            $this->syncItems($quotation, $data['items'] ?? []);
            $quotation->recalculateTotals();
        });

        return response()->json($quotation->fresh(['items.product', 'invoices']));
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return response()->json(['message' => 'Quotation deleted.']);
    }

    /** Convert quotation into a single full invoice (one invoice per quotation). */
    public function createInvoice(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'status' => 'nullable|in:draft,sent,paid,cancelled',
        ]);

        if ($quotation->invoices()->where('status', '!=', 'cancelled')->exists()) {
            return response()->json(['message' => 'هذا العرض له فاتورة بالفعل. المشروع له فاتورة واحدةحدة.'], 422);
        }

        $amount = round((float) $quotation->total, 2);
        if ($amount <= 0) {
            return response()->json(['message' => 'لا يمكن تحويل عرض بدون إجمالي.'], 422);
        }

        $invoice = Invoice::create([
            'number' => $this->nextNumber('INV'),
            'quotation_id' => $quotation->id,
            'project_id' => $quotation->project_id,
            'date' => $validated['date'] ?? now()->toDateString(),
            'client_name' => $quotation->client_name,
            'status' => $validated['status'] ?? 'draft',
            'notes' => $validated['notes'] ?? ('Invoice from ' . $quotation->number),
            'currency' => $quotation->currency,
            'amount' => $amount,
            'percent' => 100,
            'tax_percent' => (float) ($quotation->tax_percent ?? 0),
            'tax_amount' => (float) ($quotation->tax_amount ?? 0),
            'total' => $amount,
        ]);

        if ($quotation->status === 'draft') {
            $quotation->update(['status' => 'accepted']);
        }

        return response()->json($invoice->load(['quotation', 'project']), 201);
    }

    public function pdf(Quotation $quotation)
    {
        DompdfFontCache::ensureReady();
        $quotation->load(['items.product']);
        $company = $this->companySettings();
        $logoPath = $this->absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');
        $signaturePath = $this->absoluteAssetPath($company['signature'] ?? null);
        $discounts = $quotation->discountBreakdown();
        $productItems = $quotation->items->where('is_section', false)->values();
        $globalShares = Quotation::allocateGlobalDiscount(
            (float) $discounts['global_discount'],
            $productItems->map(fn (QuotationItem $i) => (float) $i->amount)->all()
        );
        $globalShareById = [];
        foreach ($productItems as $idx => $productItem) {
            $globalShareById[$productItem->id] = $globalShares[$idx] ?? 0.0;
        }
        $globalPctLabel = $quotation->globalDiscountLabelShort();
        $currency = $quotation->currency ?? 'AED';

        $pdf = Pdf::loadView('quotations.pdf', [
            'doc' => $quotation,
            'discounts' => $discounts,
            'globalPctLabel' => $globalPctLabel,
            'docTitle' => 'Estimate',
            'company' => $company,
            'companyNameEn' => $company['companyName'] ?? 'SMARTFLOW',
            'companyLegalName' => $company['legalName']
                ?? 'AL TDFUQ AL DHAKI ELECTRICITY TRANSMISSION & CONTROL EQUIPMENT INSTALLATION WORKS L.L.C',
            'companyCountry' => $company['contact']['address']['en']
                ?? ($company['seo']['location']['country'] ?? 'United Arab Emirates'),
            'trn' => trim((string) ($company['trn'] ?? ($company['taxNumber'] ?? ''))),
            'logoDataUri' => $this->toDataUri($logoPath, 160),
            'signatureDataUri' => $this->toDataUri($signaturePath, 420),
            'signatureName' => $company['signatureName'] ?? null,
            'clientName' => ArabicPdfText::shape($quotation->client_name),
            'clientNameIsArabic' => (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $quotation->client_name),
            'comments' => ArabicPdfText::shape($quotation->comments ?: ''),
            'commentsIsArabic' => (bool) preg_match('/[\x{0600}-\x{06FF}]/u', (string) $quotation->comments),
            'arabicFontUrl' => DompdfFontCache::arabicFontUrl(),
            'items' => $quotation->items->map(function (QuotationItem $item) use ($quotation, $globalShareById, $globalPctLabel, $currency) {
                $imagePath = $item->is_section ? null : $this->absoluteAssetPath($item->product?->image);
                $lineSubtotal = round((float) $item->quantity * (float) $item->rate, 2);
                $discountAmount = (float) ($item->discount_amount ?? 0);
                $discountLabel = $item->discountLabel($currency);
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
                    'discount_label' => $discountLabel,
                    'discount_amount' => $discountAmount,
                    'global_discount_label' => $globalDiscountLabel,
                    'global_discount_share' => $globalShare,
                    'amount' => $item->amount,
                    'final_amount' => $finalAmount,
                    'imageDataUri' => $this->toDataUri($imagePath, 270),
                ];
            }),
        ])->setPaper('a4');

        return $pdf->download($quotation->number . '.pdf');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'number' => 'nullable|string|max:50|unique:quotations,number,' . ($ignoreId ?: 'NULL') . ',id',
            'date' => 'required|date',
            'client_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|in:draft,sent,accepted,cancelled',
            'comments' => 'nullable|string|max:10000',
            'currency' => 'nullable|string|max:10',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.is_section' => 'nullable|boolean',
            'items.*.section_title' => 'nullable|string|max:255',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.discount_type' => 'nullable|in:percent,fixed',
            'items.*.discount_value' => 'nullable|numeric|min:0',
        ]);

        $hasProduct = false;
        foreach ($data['items'] as $i => $item) {
            $isSection = filter_var($item['is_section'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($isSection) {
                $title = trim((string) ($item['section_title'] ?? ''));
                if ($title === '') {
                    throw ValidationException::withMessages([
                        'items' => ['عنوان المجموعة مطلوب (بند #' . ($i + 1) . ')'],
                    ]);
                }
                continue;
            }
            if (empty($item['product_id'])) {
                throw ValidationException::withMessages([
                    'items' => ['كل بند منتج يحتاج product_id'],
                ]);
            }
            $hasProduct = true;
        }
        if (!$hasProduct) {
            throw ValidationException::withMessages([
                'items' => ['أضف منتجاً واحداً على الأقل تحت العناوين'],
            ]);
        }

        if (!empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) {
                $data['client_name'] = $customer->name;
            }
        }

        return $data;
    }

    private function normalizeDiscountType(mixed $type): ?string
    {
        return in_array($type, ['percent', 'fixed'], true) ? (string) $type : null;
    }

    private function normalizeDiscountValue(mixed $type, mixed $value): ?float
    {
        if (!in_array($type, ['percent', 'fixed'], true)) {
            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();
        foreach (array_values($items) as $i => $item) {
            $isSection = filter_var($item['is_section'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($isSection) {
                $title = trim((string) ($item['section_title'] ?? ''));
                if ($title === '') {
                    continue;
                }
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => null,
                    'is_section' => true,
                    'code' => null,
                    'description' => $title,
                    'quantity' => 0,
                    'rate' => 0,
                    'amount' => 0,
                    'sort_order' => $i,
                ]);
                continue;
            }

            $product = Product::find($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }
            $qty = (float) ($item['quantity'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);
            $discountType = in_array($item['discount_type'] ?? null, ['percent', 'fixed'], true)
                ? (string) $item['discount_type']
                : null;
            $discountValue = ($discountType && isset($item['discount_value']))
                ? (float) $item['discount_value']
                : null;
            $amounts = QuotationItem::computeAmount($qty, $rate, $discountType, $discountValue);
            $title = trim((string) ($product->name_ar ?: $product->name));
            $detail = ProductDescription::withoutFeaturesSection(
                trim((string) ($product->description_ar ?: $product->description ?: ''))
            );
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_id' => $product->id,
                'is_section' => false,
                'code' => $product->brand ?: ('P' . $product->id),
                'description' => $detail !== '' ? $title . "\n" . $detail : $title,
                'quantity' => $qty,
                'rate' => $rate,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $amounts['discount_amount'],
                'amount' => $amounts['amount'],
                'sort_order' => $i,
            ]);
        }
    }

    private function nextNumber(string $prefix): string
    {
        $year = now()->format('y');
        $like = $prefix . '-' . $year . '-%';
        $table = $prefix === 'INV' ? 'invoices' : 'quotations';
        $last = DB::table($table)->where('number', 'like', $like)->orderByDesc('id')->value('number');
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }
        return sprintf('%s-%s-%06d', $prefix, $year, $seq);
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
        if (preg_match('/^https?:\/\//i', $webPath)) {
            return $webPath;
        }

        $absolute = StorageUrl::toFilesystemPath($webPath);

        return ($absolute && File::exists($absolute)) ? $absolute : null;
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
}
