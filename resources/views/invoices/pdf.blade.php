<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice->number }}</title>
@include('partials.finance-pdf.styles')
</head>
<body>

@include('partials.finance-pdf.header', [
    'docTitleAr' => 'فاتورة',
    'docTitleEn' => 'INVOICE',
    'numberLabelAr' => 'رقم الفاتورة',
    'docNumber' => $invoice->number,
    'dateLabel' => $dateLabel,
    'extraMetaRows' => [
        ['label' => 'Terms / الشروط', 'value' => $terms],
        ['label' => 'Due Date / الاستحقاق', 'value' => $dueDateLabel],
    ],
    'clientLabelAr' => 'فاتورة إلى / Bill To',
])

@include('partials.finance-pdf.items')

<div class="summary-page">
@include('partials.finance-pdf.totals', [
    'grandLabel' => 'Balance Due / المستحق',
])

@if(!empty($notes))
<div class="notes-h">ملاحظات / Notes</div>
<div class="notes-b {{ !empty($notesIsArabic) ? 'ar' : '' }}">{!! nl2br(e($notes)) !!}</div>
@endif

@include('partials.finance-pdf.footer')
</div>

</body>
</html>
