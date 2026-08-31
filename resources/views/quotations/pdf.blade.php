<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $docTitleEn }} {{ $doc->number }}</title>
@include('partials.finance-pdf.styles')
</head>
<body>

@include('partials.finance-pdf.header', [
    'docTitleAr' => 'عرض أسعار',
    'docTitleEn' => 'ESTIMATE',
    'numberLabelAr' => 'رقم العرض',
    'docNumber' => $doc->number,
    'dateLabel' => \Illuminate\Support\Carbon::parse($doc->date)->format('d/m/Y'),
    'clientLabelAr' => 'عرض إلى / For',
])

@include('partials.finance-pdf.items')

<div class="summary-page">
@include('partials.finance-pdf.totals', [
    'taxPercent' => $doc->tax_percent,
    'taxAmount' => $doc->tax_amount,
    'withholdingPercent' => $doc->withholding_tax_percent,
    'withholdingAmount' => $doc->withholding_tax_amount,
    'grandLabel' => 'الإجمالي / Total',
    'grandTotal' => $doc->total,
])

@if(!empty($comments))
<div class="notes-h">ملاحظات / Comments</div>
<div class="notes-b {{ !empty($commentsIsArabic) ? 'ar' : '' }}">{!! nl2br(e($comments)) !!}</div>
@endif

@include('partials.finance-pdf.footer', [
    'dateLabel' => \Illuminate\Support\Carbon::parse($doc->date)->format('d/m/Y'),
])
</div>

</body>
</html>
