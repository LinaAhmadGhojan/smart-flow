<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ProjectPayment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Support\BrowserPdf;
use App\Support\FinanceDocumentViewData;
use App\Support\FinancePdfBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function html(Invoice $invoice)
    {
        return response()->view('finance-documents.html', FinanceDocumentViewData::forInvoice($invoice));
    }

    public function pdf(Invoice $invoice)
    {
        try {
            $data = FinanceDocumentViewData::forInvoice($invoice);
            $filename = $invoice->number . '.pdf';
            $html = view('finance-documents.html', $data)->render();

            $rendered = BrowserPdf::render(
                $html,
                2000,
                ['width' => 8.27, 'height' => 11.69, 'landscape' => false]
            );

            if ($rendered !== null) {
                return response($rendered, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Content-Length' => (string) strlen($rendered),
                ]);
            }

            return response()->json([
                'message' => 'تعذر إنشاء PDF على الخادم — استخدم التصدير من المتصفح.',
            ], 503);
        } catch (\Throwable $e) {
            Log::error('Invoice PDF export failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'تعذر تصدير PDF: ' . $e->getMessage(),
            ], 500);
        }
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
