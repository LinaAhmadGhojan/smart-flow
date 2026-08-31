@php
    $totalDiscount = round((float) ($discounts['line_discount_total'] ?? 0) + (float) ($discounts['global_discount'] ?? 0), 2);
@endphp
<div class="totals-wrap">
    <div class="totals-h">الإجماليات / Summary</div>
    <table class="tot">
        <tr>
            <td class="k">إجمالي المبلغ / Subtotal</td>
            <td class="v">{{ $currency }} {{ number_format((float)$discounts['gross_subtotal'], 2) }}</td>
        </tr>
        @if($totalDiscount > 0)
        <tr class="disc-row">
            <td class="k">الخصم / Discount</td>
            <td class="v">− {{ $currency }} {{ number_format($totalDiscount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="k">ضريبة القيمة المضافة / TAX {{ rtrim(rtrim(number_format((float)$taxPercent, 2), '0'), '.') }}%</td>
            <td class="v">{{ $currency }} {{ number_format((float)$taxAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="k">الإجمالي / Total</td>
            <td class="v">{{ $currency }} {{ number_format((float)$discounts['net_before_tax'] + (float)$taxAmount, 2) }}</td>
        </tr>
        @if((float)$withholdingPercent > 0 || (float)$withholdingAmount > 0)
        <tr>
            <td class="k">Withholding Tax {{ rtrim(rtrim(number_format((float)$withholdingPercent, 2), '0'), '.') }}%</td>
            <td class="v">{{ $currency }} {{ number_format((float)$withholdingAmount, 2) }}</td>
        </tr>
        @endif
        @if(isset($paid))
        <tr>
            <td class="k">المدفوع / Paid</td>
            <td class="v">{{ $currency }} {{ number_format((float)$paid, 2) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td class="k">{{ $grandLabel ?? 'Balance Due / المستحق' }}</td>
            <td class="v">{{ $currency }} {{ number_format((float)$grandTotal, 2) }}</td>
        </tr>
    </table>
</div>
