<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectDeliveryNote;
use App\Models\ProjectExpense;
use App\Models\ProjectPayment;
use App\Models\ProjectProfitShare;
use App\Support\ArabicPdfText;
use App\Support\CompanySettings;
use App\Support\BrowserPdf;
use App\Support\DompdfFontCache;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class ProjectFinanceController extends Controller
{
    public function indexPayments()
    {
        return response()->json(
            ProjectPayment::query()
                ->with([
                    'project:id,title,title_ar,customer_id,status',
                    'project.customer:id,name,phone,email',
                    'invoice:id,number,project_id,client_name',
                ])
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->get()
        );
    }

    public function storePaymentGlobal(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => ['required', Rule::in(ProjectPayment::TYPES)],
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:10240',
        ]);

        if (empty($data['project_id']) && empty($data['invoice_id'])) {
            return response()->json(['message' => 'اختر مشروعاً أو فاتورة.'], 422);
        }

        [$projectId, $invoiceId] = $this->resolvePaymentLinks(
            $data['project_id'] ?? null,
            $data['invoice_id'] ?? null
        );

        if (!$projectId && !$invoiceId) {
            return response()->json(['message' => 'تعذر ربط الدفعة — اربط الفاتورة بمشروع أو اختر مشروعاً.'], 422);
        }

        $project = $projectId ? Project::findOrFail($projectId) : null;
        if ($project && $project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $this->moveUpload($request->file('receipt'), 'projects/receipts');
        }

        $payment = ProjectPayment::create([
            'project_id' => $projectId,
            'invoice_id' => $invoiceId,
            'amount' => $data['amount'],
            'payment_type' => $data['payment_type'],
            'paid_at' => $data['paid_at'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $path,
        ]);

        $payment->load([
            'project:id,title,title_ar,customer_id,status',
            'project.customer:id,name,phone,email',
            'invoice:id,number,project_id,client_name',
        ]);

        return response()->json([
            'payment' => $payment,
            'finance' => $project?->fresh()->finance_summary,
        ], 201);
    }

    public function showFinance(Project $project)
    {
        return response()->json($project->finance_summary);
    }

    public function storePayment(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => ['required', Rule::in(ProjectPayment::TYPES)],
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:10240',
        ]);

        $invoiceId = $data['invoice_id'] ?? null;
        if ($invoiceId) {
            $invoice = Invoice::findOrFail($invoiceId);
            if ($invoice->project_id && (int) $invoice->project_id !== (int) $project->id) {
                return response()->json(['message' => 'الفاتورة لا تتبع هذا المشروع.'], 422);
            }
            if (!$invoice->project_id) {
                $invoice->update(['project_id' => $project->id]);
            }
        }

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $this->moveUpload($request->file('receipt'), 'projects/receipts');
        }

        $payment = ProjectPayment::create([
            'project_id' => $project->id,
            'invoice_id' => $invoiceId,
            'amount' => $data['amount'],
            'payment_type' => $data['payment_type'],
            'paid_at' => $data['paid_at'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $path,
        ]);

        $payment->load([
            'project:id,title,title_ar,customer_id,status',
            'project.customer:id,name,phone,email',
            'invoice:id,number,project_id,client_name',
        ]);

        return response()->json([
            'payment' => $payment,
            'finance' => $project->fresh()->finance_summary,
        ], 201);
    }

    public function updatePayment(Request $request, Project $project, ProjectPayment $payment)
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_type' => ['sometimes', Rule::in(ProjectPayment::TYPES)],
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:10240',
        ]);

        if (array_key_exists('invoice_id', $data) && $data['invoice_id']) {
            $invoice = Invoice::findOrFail($data['invoice_id']);
            if ($invoice->project_id && (int) $invoice->project_id !== (int) $project->id) {
                return response()->json(['message' => 'الفاتورة لا تتبع هذا المشروع.'], 422);
            }
        }

        if ($request->hasFile('receipt')) {
            $this->unlinkStoragePath($payment->receipt_path);
            $data['receipt_path'] = $this->moveUpload($request->file('receipt'), 'projects/receipts');
        }

        unset($data['receipt']);
        $payment->update($data);

        $payment->load([
            'project:id,title,title_ar,customer_id,status',
            'project.customer:id,name,phone,email',
            'invoice:id,number,project_id,client_name',
        ]);

        return response()->json([
            'payment' => $payment,
            'finance' => $project->fresh()->finance_summary,
        ]);
    }

    public function destroyPayment(Project $project, ProjectPayment $payment)
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }
        $this->unlinkStoragePath($payment->receipt_path);
        $payment->delete();

        return response()->json(['finance' => $project->fresh()->finance_summary]);
    }

    public function htmlPayment(Project $project, ProjectPayment $payment)
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }

        return response()->view(
            'payments.receipt-html',
            $this->paymentReceiptViewData($project, $payment, false)
        );
    }

    public function pdfPayment(Project $project, ProjectPayment $payment)
    {
        DompdfFontCache::ensureReady();
        if ($payment->project_id !== $project->id) {
            abort(404);
        }

        $webData = $this->paymentReceiptViewData($project, $payment, false);
        $filename = $webData['receiptNumber'] . '.pdf';

        // Printing the browser view keeps Arabic shaping and the layout identical
        // to what the user sees; Dompdf can only approximate both.
        $rendered = BrowserPdf::render(
            view('payments.receipt-html', $webData + [
                'fontEmbedCss' => $this->receiptFontEmbedCss(),
            ])->render()
        );

        if ($rendered !== null) {
            return response($rendered, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => (string) strlen($rendered),
            ]);
        }

        $data = $this->paymentReceiptViewData($project, $payment, true);
        $pdf = Pdf::loadView('payments.receipt-html', $data)->setPaper('a5', 'landscape');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('defaultFont', 'Cairo');

        return $pdf->download($filename);
    }

    /** @return array<string, mixed> */
    private function paymentReceiptViewData(Project $project, ProjectPayment $payment, bool $forPdf): array
    {
        $project->loadMissing(['customer']);
        $company = $this->companySettings();
        $logoPath = $this->absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');

        $clientName = $project->customer?->name ?: '—';
        $projectTitle = $project->title_ar ?: $project->title;
        $forRaw = trim((string) ($payment->notes ?: ''));
        if ($forRaw === '') {
            $forRaw = 'دفعة على مشروع: ' . $projectTitle;
        }

        $type = (string) $payment->payment_type;
        $isCash = $type === 'cash';
        $isCheque = $type === 'cheque';
        $isBank = in_array($type, ['bank', 'transfer', 'card', 'other'], true);

        $amount = (float) $payment->amount;
        $amountWords = $this->amountToArabicWords($amount);
        $receiptNumber = 'RCP-' . $project->id . '-' . str_pad((string) $payment->id, 3, '0', STR_PAD_LEFT);

        $contact = is_array($company['contact'] ?? null) ? $company['contact'] : [];
        $addressAr = is_array($contact['address'] ?? null)
            ? (($contact['address']['ar'] ?? null) ?: ($contact['address']['en'] ?? 'UAE'))
            : 'الإمارات العربية المتحدة';

        $companyNameAr = (string) ($company['companyNameAr'] ?? $company['companyName'] ?? 'SMART FLOW');
        $tagAr = (string) ($company['taglineAr'] ?? $company['footerDescAr'] ?? 'للأنظمة الذكية والحلول التقنية');

        $shape = static fn (string $text) => $forPdf ? ArabicPdfText::shape($text) : $text;

        $fontsDir = resource_path('fonts');
        $boldFont = $fontsDir . DIRECTORY_SEPARATOR . 'Cairo-Bold.ttf';
        $regFont = $fontsDir . DIRECTORY_SEPARATOR . 'Cairo-Regular.ttf';
        if (!is_file($boldFont)) {
            $boldFont = $fontsDir . DIRECTORY_SEPARATOR . 'Tajawal-Bold.ttf';
        }
        if (!is_file($regFont)) {
            $regFont = $fontsDir . DIRECTORY_SEPARATOR . 'Tajawal-Regular.ttf';
        }
        if (!is_file($boldFont)) {
            $boldFont = $fontsDir . DIRECTORY_SEPARATOR . 'NotoSansArabic-Bold.ttf';
        }
        if (!is_file($regFont)) {
            $regFont = $fontsDir . DIRECTORY_SEPARATOR . 'NotoSansArabic-Regular.ttf';
        }
        $toFileUrl = static fn (string $path) => 'file:///' . str_replace('\\', '/', $path);
        $icons = $this->receiptIconDataUris();

        return [
            'receiptNumber' => $receiptNumber,
            'dateLabel' => Carbon::parse($payment->paid_at)->format('d / m / Y'),
            'logoDataUri' => $this->toDataUri($logoPath, 100),
            'companyNameAr' => $shape($companyNameAr),
            'companyTagAr' => $shape($tagAr),
            'clientName' => $shape($clientName),
            'amountWords' => $shape($amountWords),
            'forLabel' => $shape($forRaw),
            'amountNumber' => number_format($amount, 2, '.', ','),
            'refNo' => '',
            'bankName' => '',
            'isCash' => $isCash,
            'isBank' => $isBank,
            'isCheque' => $isCheque,
            'phone' => $this->formatUaePhone((string) ($contact['phone'] ?? '+971')),
            'email' => (string) ($contact['email'] ?? 'info@smartflow.ae'),
            'addressAr' => $shape($addressAr),
            'lblTitle' => $shape('وصل استلام مالي'),
            'lblReceiptNo' => $shape('رقم الوصل'),
            'lblDate' => $shape('التاريخ'),
            'lblReceivedFrom' => $shape('استلمنا من السيد/المؤسسة'),
            'lblAmountWords' => $shape('مبلغ وقدره'),
            'lblFor' => $shape('وذلك مقابل'),
            'lblPayMethod' => $shape('طريقة الدفع'),
            'lblCash' => $shape('كاش'),
            'lblBankTransfer' => $shape('تحويل بنكي'),
            'lblCheque' => $shape('شيك'),
            'lblAmount' => $shape('المبلغ'),
            'lblRef' => $shape('رقم الشيك / التحويل'),
            'lblBank' => $shape('البنك'),
            'lblDateCol' => $shape('التاريخ'),
            'lblCurrency' => $shape('درهم إماراتي'),
            'lblAccountant' => $shape('توقيع المحاسب'),
            'lblReceiver' => $shape('توقيع المستلم'),
            'lblThanks' => $shape('شكراً لتعاملكم معنا'),
            'fontBoldUrl' => $toFileUrl($boldFont),
            'fontRegularUrl' => $toFileUrl($regFont),
            'iconCash' => $icons['cash'],
            'iconBank' => $icons['bank'],
            'iconCheque' => $icons['cheque'],
            'iconPhone' => $icons['phone'],
            'iconEmail' => $icons['email'],
            'iconLocation' => $icons['location'],
            'waveSvg' => $icons['wave'],
            'forPdf' => $forPdf,
        ];
    }

    /** Group a UAE number as "+971 56 256 6232" so it reads cleanly on the receipt. */
    private function formatUaePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = '971' . substr($digits, 1);
        }
        if (!str_starts_with($digits, '971') || strlen($digits) !== 12) {
            return trim($phone);
        }

        return sprintf(
            '+971 %s %s %s',
            substr($digits, 3, 2),
            substr($digits, 5, 3),
            substr($digits, 8, 4)
        );
    }

    /**
     * Inline the receipt fonts so a headless browser renders the same glyphs
     * without depending on Google Fonts being reachable.
     */
    private function receiptFontEmbedCss(): string
    {
        $faces = [
            ['Cairo', 400, 'Cairo-Regular.ttf'],
            ['Cairo', 700, 'Cairo-Bold.ttf'],
            ['CairoFallback', 400, 'NotoSansArabic-Regular.ttf'],
            ['CairoFallback', 700, 'NotoSansArabic-Bold.ttf'],
        ];

        $css = '';
        foreach ($faces as [$family, $weight, $file]) {
            $path = resource_path('fonts/' . $file);
            if (!is_file($path)) {
                continue;
            }

            $css .= sprintf(
                "@font-face{font-family:'%s';font-style:normal;font-weight:%d;font-display:block;"
                . "src:url(data:font/ttf;base64,%s) format('truetype');}\n",
                $family,
                $weight,
                base64_encode((string) file_get_contents($path))
            );
        }

        return $css;
    }

    /** SVG icons for payment receipt (DomPDF-safe data URIs). */
    private function receiptIconDataUris(): array
    {
        $blue = '#1a437f';
        $light = '#5b8fd4';
        $iconsDir = resource_path('images/receipt');

        $pngUri = static function (string $path) {
            if (!is_file($path)) {
                return null;
            }
            $mime = 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
        };

        $svg = static fn (string $body, int $w = 36, int $h = 36) => 'data:image/svg+xml;base64,' . base64_encode(
            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 36 36" fill="none">' . $body . '</svg>'
        );

        return [
            'cash' => $svg(
                '<rect x="3" y="18" width="24" height="12" rx="1.6" stroke="' . $blue . '" stroke-width="1.5"/>'
                . '<rect x="6" y="13" width="24" height="12" rx="1.6" stroke="' . $blue . '" stroke-width="1.5" fill="#fff"/>'
                . '<rect x="9" y="8" width="24" height="12" rx="1.6" stroke="' . $blue . '" stroke-width="1.5" fill="#fff"/>'
                . '<ellipse cx="21" cy="14" rx="3.6" ry="2.8" stroke="' . $blue . '" stroke-width="1.4"/>'
            ),
            'bank' => $svg(
                '<path d="M18 5 L33 15 H3 Z" fill="' . $blue . '"/>'
                . '<rect x="5" y="15" width="26" height="2.4" fill="' . $blue . '"/>'
                . '<rect x="8" y="18.5" width="4" height="10" fill="' . $blue . '"/>'
                . '<rect x="16" y="18.5" width="4" height="10" fill="' . $blue . '"/>'
                . '<rect x="24" y="18.5" width="4" height="10" fill="' . $blue . '"/>'
                . '<rect x="4" y="29" width="28" height="3" fill="' . $blue . '"/>'
            ),
            'cheque' => $svg(
                '<rect x="3" y="10" width="24" height="16" rx="1.6" stroke="' . $blue . '" stroke-width="1.5" fill="#fff"/>'
                . '<rect x="6" y="13" width="5" height="3.2" stroke="' . $blue . '" stroke-width="1.1"/>'
                . '<line x1="13" y1="14.5" x2="23" y2="14.5" stroke="' . $blue . '" stroke-width="1.2"/>'
                . '<line x1="6" y1="20" x2="20" y2="20" stroke="' . $blue . '" stroke-width="1.2"/>'
                . '<path d="M24 24 L31.5 13.5 L33.5 15.2 L26 25.8 Z" fill="' . $blue . '"/>'
                . '<line x1="31.5" y1="13.5" x2="33.8" y2="11" stroke="' . $blue . '" stroke-width="1.4" stroke-linecap="round"/>'
            ),
            'phone' => $svg(
                '<path d="M9.5 3.5 C8.2 3.5 7.2 4.6 7.4 5.9 C7.9 9.4 9.6 12.6 12.2 15.2 C14.8 17.8 18 19.5 21.5 20 C22.8 20.2 23.9 19.2 23.9 17.9 V15.8 C23.9 15.2 23.5 14.7 22.9 14.5 L20.3 13.7 C19.8 13.5 19.2 13.7 18.9 14.1 L17.7 15.6 C15.6 14.6 13.8 12.8 12.8 10.7 L14.3 9.5 C14.7 9.2 14.9 8.6 14.7 8.1 L13.9 5.5 C13.7 4.9 13.2 4.5 12.6 4.5 Z" fill="' . $blue . '"/>',
                12, 12
            ),
            'email' => $svg(
                '<rect x="2.2" y="3.6" width="11.6" height="8.6" rx="1.1" stroke="' . $blue . '" stroke-width="1.3"/>'
                . '<path d="M3 4.6 L8 8.2 L13 4.6" stroke="' . $blue . '" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>',
                12, 12
            ),
            'location' => $svg(
                '<path d="M8 1.8 C5.4 1.8 3.3 4 3.3 6.7 C3.3 10.4 8 15.4 8 15.4 C8 15.4 12.7 10.4 12.7 6.7 C12.7 4 10.6 1.8 8 1.8 Z" fill="' . $blue . '"/>'
                . '<circle cx="8" cy="6.6" r="1.7" fill="#fff"/>',
                12, 12
            ),
            'wave' => $pngUri($iconsDir . DIRECTORY_SEPARATOR . 'wave.png') ?: 'data:image/svg+xml;base64,' . base64_encode(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="89" viewBox="0 0 1024 89" preserveAspectRatio="none">'
                . '<path d="M0,18 C70,38 160,50 260,58 C360,66 460,70 512,71 C620,70 780,52 1024,11 L1024,89 L0,89 Z" fill="' . $light . '"/>'
                . '<path d="M0,31 C90,48 200,60 340,67 C480,73 620,68 760,55 C860,45 950,28 1024,11 L1024,89 L0,89 Z" fill="' . $blue . '"/>'
                . '</svg>'
            ),
        ];
    }

    /** Convert AED amount to Arabic words (e.g. ألفان وخمسمائة درهم إماراتي فقط لا غير). */
    private function amountToArabicWords(float $amount): string
    {
        $amount = round($amount, 2);
        $int = (int) floor($amount);
        $fils = (int) round(($amount - $int) * 100);

        $words = $this->arabicIntegerWords($int);
        if ($words === '') {
            $words = 'صفر';
        }

        $out = $words . ' درهم إماراتي';
        if ($fils > 0) {
            $filsWords = $this->arabicIntegerWords($fils);
            $out .= ' و' . $filsWords . ' فلس';
        }
        return $out . ' فقط لا غير';
    }

    private function arabicIntegerWords(int $n): string
    {
        if ($n < 0) {
            return '';
        }
        if ($n === 0) {
            return 'صفر';
        }

        $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة',
            'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
        $tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        $hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة'];

        $parts = [];

        $millions = intdiv($n, 1000000);
        $n %= 1000000;
        if ($millions === 1) {
            $parts[] = 'مليون';
        } elseif ($millions === 2) {
            $parts[] = 'مليونان';
        } elseif ($millions >= 3 && $millions <= 10) {
            $parts[] = $ones[$millions] . ' ملايين';
        } elseif ($millions > 10) {
            $parts[] = $this->arabicIntegerWords($millions) . ' مليون';
        }

        $thousands = intdiv($n, 1000);
        $n %= 1000;
        if ($thousands === 1) {
            $parts[] = 'ألف';
        } elseif ($thousands === 2) {
            $parts[] = 'ألفان';
        } elseif ($thousands >= 3 && $thousands <= 10) {
            $parts[] = $ones[$thousands] . ' آلاف';
        } elseif ($thousands > 10) {
            $parts[] = $this->arabicIntegerWords($thousands) . ' ألف';
        }

        $h = intdiv($n, 100);
        $n %= 100;
        if ($h > 0) {
            $parts[] = $hundreds[$h];
        }

        if ($n > 0) {
            if ($n < 20) {
                $parts[] = $ones[$n];
            } else {
                $t = intdiv($n, 10);
                $o = $n % 10;
                if ($o > 0) {
                    $parts[] = $ones[$o] . ' و' . $tens[$t];
                } else {
                    $parts[] = $tens[$t];
                }
            }
        }

        return implode(' و', array_filter($parts));
    }

    public function storeExpense(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:10240',
        ]);

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $this->moveUpload($request->file('receipt'), 'projects/expense-receipts');
        }

        $expense = ProjectExpense::create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'amount' => $data['amount'],
            'spent_at' => $data['spent_at'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $path,
        ]);

        return response()->json([
            'expense' => $expense,
            'finance' => $project->fresh()->finance_summary,
        ], 201);
    }

    public function destroyExpense(Project $project, ProjectExpense $expense)
    {
        if ($expense->project_id !== $project->id) {
            abort(404);
        }
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }
        $this->unlinkStoragePath($expense->receipt_path);
        $expense->delete();

        return response()->json(['finance' => $project->fresh()->finance_summary]);
    }

    public function updateCapital(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'capital_amount' => 'required|numeric|min:0.01',
        ]);

        $project->update(['capital_amount' => $data['capital_amount']]);

        return response()->json([
            'capital_amount' => (float) $project->capital_amount,
            'finance' => $project->fresh()->finance_summary,
        ]);
    }

    public function storeProfitShare(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'share_type' => ['required', Rule::in(ProjectProfitShare::TYPES)],
            'value' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($data['share_type'] === 'percent' && (float) $data['value'] > 100) {
            return response()->json(['message' => 'النسبة لا تتجاوز 100%'], 422);
        }

        if ($data['share_type'] === 'capital') {
            $projectCapital = (float) $project->contract_value;
            if ($projectCapital <= 0) {
                return response()->json(['message' => 'أضف فاتورة للمشروع أولاً لاستخدام رأس المال'], 422);
            }
            if ((float) $data['value'] > $projectCapital) {
                return response()->json(['message' => 'مساهمة الشريك لا تتجاوز قيمة الفاتورة (رأس المال)'], 422);
            }
        }

        $share = ProjectProfitShare::create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'share_type' => $data['share_type'],
            'value' => $data['value'],
            'notes' => $data['notes'] ?? null,
            'sort_order' => (int) $project->profitShares()->max('sort_order') + 1,
        ]);

        $finance = $project->fresh()->finance_summary;
        $row = collect($finance['shares'])->firstWhere('id', $share->id);

        return response()->json([
            'share' => $row ?: $share,
            'finance' => $finance,
        ], 201);
    }

    public function destroyProfitShare(Project $project, ProjectProfitShare $profitShare)
    {
        if ($profitShare->project_id !== $project->id) {
            abort(404);
        }
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }
        $profitShare->delete();

        return response()->json(['finance' => $project->fresh()->finance_summary]);
    }

    public function indexDeliveryNotes()
    {
        return response()->json(
            ProjectDeliveryNote::query()
                ->with([
                    'project:id,title,title_ar,customer_id,status',
                    'project.customer:id,name,phone,email',
                ])
                ->orderByDesc('delivered_at')
                ->orderByDesc('id')
                ->get()
        );
    }

    public function storeDeliveryNote(Request $request, Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'delivered_at' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'received_by' => 'nullable|string|max:255',
            'delivered_by' => 'nullable|string|max:255',
            'items' => 'nullable',
            'number' => 'nullable|string|max:50',
        ]);

        $items = $data['items'] ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }
        $clean = [];
        foreach ((array) $items as $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $clean[] = [
                'description' => $desc,
                'quantity' => (float) ($row['quantity'] ?? 1),
                'unit' => trim((string) ($row['unit'] ?? '')) ?: null,
                'code' => trim((string) ($row['code'] ?? $row['model'] ?? '')) ?: null,
                'note' => trim((string) ($row['note'] ?? $row['notes'] ?? '')) ?: null,
            ];
        }

        if (!$clean) {
            return response()->json(['message' => 'أضف بنداً واحداً على الأقل للتسليم'], 422);
        }

        $note = ProjectDeliveryNote::create([
            'project_id' => $project->id,
            'number' => $data['number'] ?? ('DN-' . $project->id . '-' . str_pad((string) (ProjectDeliveryNote::where('project_id', $project->id)->count() + 1), 3, '0', STR_PAD_LEFT)),
            'title' => $data['title'] ?? 'Delivery Note',
            'delivered_at' => $data['delivered_at'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'received_by' => $data['received_by'] ?? null,
            'delivered_by' => $data['delivered_by'] ?? null,
            'items' => $clean,
        ]);

        return response()->json($note->load('project.customer'), 201);
    }

    public function showDeliveryNote(Project $project, ProjectDeliveryNote $deliveryNote)
    {
        if ($deliveryNote->project_id !== $project->id) {
            abort(404);
        }

        $deliveryNote->load(['project.customer']);

        return response()->json($deliveryNote);
    }

    public function htmlDeliveryNote(Project $project, ProjectDeliveryNote $deliveryNote)
    {
        if ($deliveryNote->project_id !== $project->id) {
            abort(404);
        }

        return response()->view(
            'delivery-notes.html',
            $this->deliveryNoteViewData($project, $deliveryNote)
        );
    }

    public function pdfDeliveryNote(Project $project, ProjectDeliveryNote $deliveryNote)
    {
        DompdfFontCache::ensureReady();
        if ($deliveryNote->project_id !== $project->id) {
            abort(404);
        }

        $data = $this->deliveryNoteViewData($project, $deliveryNote);
        $filename = ($data['noteNumber'] ?: 'delivery-note') . '.pdf';

        $rendered = BrowserPdf::render(
            view('delivery-notes.html', $data)->render()
        );

        if ($rendered !== null) {
            return response($rendered, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => (string) strlen($rendered),
            ]);
        }

        $pdf = Pdf::loadView('delivery-notes.html', $data)->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download($filename);
    }

    /** @return array<string, mixed> */
    private function deliveryNoteViewData(Project $project, ProjectDeliveryNote $note): array
    {
        $project->loadMissing(['customer', 'contacts']);
        $company = $this->companySettings();
        $logoPath = $this->absoluteAssetPath($company['logo'] ?? null) ?? public_path('logo.jpeg');
        $signaturePath = $this->absoluteAssetPath($company['signature'] ?? null);
        $contact = is_array($company['contact'] ?? null) ? $company['contact'] : [];
        $addressAr = is_array($contact['address'] ?? null)
            ? (($contact['address']['ar'] ?? null) ?: ($contact['address']['en'] ?? 'UAE'))
            : 'الإمارات العربية المتحدة';

        $customer = $project->customer;
        $siteContact = $project->contacts->first();
        $items = [];
        foreach ((array) ($note->items ?: []) as $row) {
            $qtyRaw = $row['quantity'] ?? '';
            $qty = ($qtyRaw === '' || $qtyRaw === null)
                ? ''
                : rtrim(rtrim(number_format((float) $qtyRaw, 2, '.', ''), '0'), '.');
            $items[] = [
                'description' => (string) ($row['description'] ?? ''),
                'code' => (string) ($row['code'] ?? $row['model'] ?? ''),
                'quantity' => $qty,
                'unit' => (string) ($row['unit'] ?? ''),
                'note' => (string) ($row['note'] ?? $row['notes'] ?? ''),
            ];
        }
        while (count($items) < 8) {
            $items[] = ['description' => '', 'code' => '', 'quantity' => '', 'unit' => '', 'note' => ''];
        }

        $icons = $this->receiptIconDataUris();
        $dash = '—';
        $clientPhone = $this->formatUaePhone((string) ($customer?->phone ?: ''));
        $deliveryPhone = $this->formatUaePhone((string) ($siteContact?->phone ?: ($customer?->phone ?: '')));

        return [
            'noteNumber' => (string) ($note->number ?: 'DN'),
            'dateLabel' => Carbon::parse($note->delivered_at)->format('d / m / Y'),
            'logoDataUri' => $this->toDataUri($logoPath, 240),
            'signatureDataUri' => $this->toDataUri($signaturePath, 180),
            'companyNameAr' => (string) ($company['companyNameAr'] ?? $company['companyName'] ?? 'SMART FLOW'),
            'phone' => $this->formatUaePhone((string) ($contact['phone'] ?? '+971')),
            'email' => (string) ($contact['email'] ?? 'info@smartflow.ae'),
            'addressAr' => (string) $addressAr,
            'clientName' => $customer?->name ?: $dash,
            'clientCompany' => '',
            'clientPhone' => $clientPhone,
            'clientMobile' => $clientPhone,
            'clientEmail' => (string) ($customer?->email ?: ''),
            'clientAddress' => (string) ($project->location ?: $addressAr),
            'deliveryTo' => (string) (($project->title_ar ?: $project->title) ?: $dash),
            'deliveryAddress' => (string) ($project->location ?: ''),
            'deliveryPhone' => $deliveryPhone,
            'receivedBy' => (string) ($note->received_by ?: ''),
            'deliveredBy' => (string) ($note->delivered_by ?: ($company['signatureName'] ?? 'SmartFlow')),
            'deliveryMethod' => '',
            'deliveryNotes' => (string) ($note->title ?: ''),
            'notes' => (string) ($note->notes ?: ''),
            'items' => $items,
            'totalAmount' => '',
            'discountAmount' => '',
            'vatAmount' => '',
            'grandTotal' => '',
            'iconCash' => $icons['cash'],
            'iconBank' => $icons['bank'],
            'iconCheque' => $icons['cheque'],
            'iconPhone' => $icons['phone'],
            'iconEmail' => $icons['email'],
            'iconLocation' => $icons['location'],
            'waveSvg' => $icons['wave'],
            'fontEmbedCss' => $this->receiptFontEmbedCss(),
        ];
    }

    public function destroyDeliveryNote(Project $project, ProjectDeliveryNote $deliveryNote)
    {
        if ($deliveryNote->project_id !== $project->id) {
            abort(404);
        }
        if ($project->status === 'completed') {
            return response()->json(['message' => 'المشروع مكتمل ولا يمكن تعديله.'], 422);
        }
        $deliveryNote->delete();

        return response()->json(['message' => 'deleted']);
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
        $absolute = public_path(ltrim($webPath, '/'));
        return File::exists($absolute) ? $absolute : null;
    }

    private function toDataUri(?string $absolutePath, int $maxSide = 0): ?string
    {
        if (!$absolutePath || !File::exists($absolutePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (function_exists('imagecreatefromstring')) {
            $raw = @File::get($absolutePath);
            $src = $raw ? @imagecreatefromstring($raw) : false;
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
                if ($ext === 'png') {
                    imagepng($dst, null, 6);
                    $bin = ob_get_clean();
                    imagedestroy($dst);
                    if ($bin) {
                        return 'data:image/png;base64,' . base64_encode($bin);
                    }
                } else {
                    imagejpeg($dst, null, 92);
                    $bin = ob_get_clean();
                    imagedestroy($dst);
                    if ($bin) {
                        return 'data:image/jpeg;base64,' . base64_encode($bin);
                    }
                }
            }
        }

        if ($ext === 'webp') {
            return null;
        }
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(File::get($absolutePath));
    }

    private function moveUpload($file, string $folder): ?string
    {
        $filename = time() . '-' . uniqid() . '-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $destination = public_path('storage/' . $folder);
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $file->move($destination, $filename);

        return '/storage/' . $folder . '/' . $filename;
    }

    private function unlinkStoragePath(?string $webPath): void
    {
        if (!$webPath || !str_starts_with($webPath, '/storage/')) {
            return;
        }
        $absolute = public_path(str_replace('/storage/', 'storage/', $webPath));
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    /** @return array{0: ?int, 1: ?int} */
    private function resolvePaymentLinks(?int $projectId, ?int $invoiceId): array
    {
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                if (!$projectId && $invoice->project_id) {
                    $projectId = (int) $invoice->project_id;
                } elseif ($projectId && $invoice->project_id && (int) $invoice->project_id !== $projectId) {
                    return [null, null];
                }
            }
        }

        return [$projectId ?: null, $invoiceId ?: null];
    }
}
