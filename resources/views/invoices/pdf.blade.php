<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Invoice {{ $invoice->number }}</title>
<style>
    @font-face {
        font-family: 'ArabicReport';
        src: url('{{ $arabicFontUrl }}') format('truetype');
    }
    @page { margin: 36px 40px 48px 40px; }
    body { font-family: DejaVu Sans, 'ArabicReport', sans-serif; color: #333; font-size: 11px; line-height: 1.4; }
    .ar { font-family: 'ArabicReport', DejaVu Sans, sans-serif; direction: rtl; unicode-bidi: bidi-override; }
    .watermark {
        position: fixed; top: 240px; left: 10px; width: 280px; opacity: 0.07; z-index: -1;
    }
    .watermark img { width: 260px; }
    .top { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .top td { vertical-align: top; border: none; padding: 0; }
    .left { width: 34%; }
    .center { width: 32%; text-align: center; }
    .right { width: 34%; text-align: right; }
    .center img { width: 175px; height: auto; max-height: 145px; object-fit: contain; }
    .country { margin: 0 0 2px; color: #444; }
    .trn { margin: 0; color: #777; font-size: 10px; }
    .doc-title { font-size: 30px; font-weight: bold; margin: 0 0 10px; color: #222; }
    .meta { margin: 3px 0; font-size: 11px; }
    .bill-to {
        border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;
        padding: 8px 0; margin: 8px 0 12px; font-size: 12px;
    }
    .rule { border: none; border-top: 2px solid #2177cf; margin: 0 0 8px; }
    .items { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .items th {
        background: #f3f3f3;
        border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;
        text-align: left; padding: 7px 6px; font-size: 11px; color: #333;
        font-weight: bold;
    }
    .items td { border-bottom: 1px solid #eee; padding: 8px 6px; vertical-align: top; }
    .items .num { text-align: right; }
    .items .thumb { width: 126px; height: 126px; object-fit: cover; border: 1px solid #ddd; border-radius: 6px; }
    .items .section-row td {
        background: #e8eef5; font-weight: bold; font-size: 13px; padding: 10px 8px;
        border-top: 1px solid #c5d4e8; border-bottom: 1px solid #c5d4e8; color: #1e3a5f;
        text-align: center;
    }
    .disc { color: #c0392b; font-weight: bold; }
    .disc-val { color: #c0392b; font-weight: bold; white-space: nowrap; }
    .was-price { color: #999; text-decoration: line-through; font-size: 9px; display: block; }
    .totals tr.disc-row .label,
    .totals tr.disc-row .val { color: #c0392b; font-weight: bold; }
    .items .thumb-empty { width: 126px; height: 126px; background: #f5f5f5; border: 1px solid #eee; border-radius: 6px; }
    .parts-sub { width: 100%; border-collapse: collapse; margin: 4px 0 16px; }
    .parts-sub td { padding: 6px; font-weight: bold; }
    .parts-sub .label { text-align: right; }
    .parts-sub .val { text-align: right; white-space: nowrap; width: 140px; }
    .summary-page { page-break-before: always; break-before: page; }
    .totals { width: 48%; margin-left: auto; border-collapse: collapse; }
    .totals td { padding: 5px 6px; }
    .totals .label { text-align: left; color: #555; }
    .totals .val { text-align: right; white-space: nowrap; width: 140px; }
    .totals .grand td {
        background: #eaf4fc;
        border-top: 2px solid #2177cf;
        font-size: 14px; font-weight: bold; padding-top: 8px; padding-bottom: 8px; color: #222;
    }
    .section { font-weight: bold; margin: 16px 0 6px; font-size: 12px; }
    .notes { white-space: pre-wrap; }
    .sign-table { width: 100%; border-collapse: collapse; margin-top: 40px; table-layout: fixed; }
    .sign-table td {
        width: 50%; vertical-align: top; border: none; padding: 0 18px;
        text-align: center;
    }
    .sign-table td:first-child { padding-left: 0; }
    .sign-table td:last-child { padding-right: 0; }
    .sig-box {
        height: 220px; width: 100%; display: table; margin: 0 auto 8px;
    }
    .sig-img {
        display: table-cell; vertical-align: bottom; text-align: center;
        height: 220px; width: 100%;
    }
    .sig-img img {
        width: 200px; height: auto; max-width: 200px; max-height: 210px;
        display: inline-block; object-fit: contain;
    }
    .sig-company {
        font-size: 9px; color: #555; line-height: 1.35; margin: 0 auto;
        text-transform: uppercase; width: 90%; text-align: center; min-height: 28px;
    }
    .sig-line {
        border-top: 1.5px solid #222; padding-top: 8px; margin: 0 auto;
        font-size: 11px; color: #333; font-weight: bold; width: 85%;
    }
    .sig-name {
        font-size: 11px; color: #444; margin-top: 6px; line-height: 1.35;
        width: 85%; margin-left: auto; margin-right: auto; min-height: 28px;
    }
    .page-foot {
        position: fixed; bottom: -28px; left: 0; right: 0;
        text-align: center; font-size: 9px; color: #888;
    }
</style>
</head>
<body>

@if(!empty($logoDataUri))
<div class="watermark"><img src="{{ $logoDataUri }}" alt=""></div>
@endif

<table class="top">
    <tr>
        <td class="left">
            <p class="country">{{ $companyCountry }}</p>
            @if(!empty($trn))
                <p class="trn">TRN: {{ $trn }}</p>
            @endif
        </td>
        <td class="center">
            @if(!empty($logoDataUri))
                <img src="{{ $logoDataUri }}" alt="logo" width="175" style="width:175px;height:auto;max-height:145px;object-fit:contain;">
            @endif
        </td>
        <td class="right">
            <p class="doc-title">Invoice</p>
            <p class="meta"><strong>Invoice No:</strong> {{ $invoice->number }}</p>
            <p class="meta"><strong>Date:</strong> {{ $dateLabel }}</p>
            <p class="meta"><strong>Terms:</strong> {{ $terms }}</p>
            <p class="meta"><strong>Due Date:</strong> {{ $dueDateLabel }}</p>
        </td>
    </tr>
</table>

<div class="bill-to">
    <strong>Bill To:</strong>
    <span class="{{ $clientNameIsArabic ? 'ar' : '' }}">{{ $clientName }}</span>
</div>

<hr class="rule">

@if(count($items))
<table class="items">
    <thead>
        <tr>
            <th style="width:10%">Code</th>
            <th style="width:30%">Description</th>
            <th style="width:18%">Image</th>
            <th style="width:8%" class="num">Quantity</th>
            <th style="width:12%" class="num">Rate</th>
            <th style="width:14%" class="num">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        @if(!empty($item['is_section']))
        <tr class="section-row">
            <td colspan="6" style="text-align:center;" class="{{ $item['descriptionIsArabic'] ? 'ar' : '' }}">{{ $item['description'] }}</td>
        </tr>
        @else
        <tr>
            <td>{{ $item['code'] }}</td>
            <td class="{{ $item['descriptionIsArabic'] ? 'ar' : '' }}">{!! nl2br(e($item['description'])) !!}</td>
            <td>
                @if(!empty($item['imageDataUri']))
                    <img class="thumb" src="{{ $item['imageDataUri'] }}" alt="">
                @else
                    <div class="thumb-empty"></div>
                @endif
            </td>
            <td class="num">{{ rtrim(rtrim(number_format((float)$item['quantity'], 2, '.', ''), '0'), '.') }}</td>
            <td class="num">{{ $currency }} {{ number_format((float)$item['rate'], 2) }}</td>
            <td class="num">{{ $currency }} {{ number_format((float)($item['final_amount'] ?? $item['amount']), 2) }}</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@endif

<div class="summary-page">
@php
    $totalDiscount = round((float) ($discounts['line_discount_total'] ?? 0) + (float) ($discounts['global_discount'] ?? 0), 2);
@endphp
<table class="totals">
    <tr>
        <td class="label">Subtotal</td>
        <td class="val">{{ $currency }} {{ number_format((float)$discounts['gross_subtotal'], 2) }}</td>
    </tr>
    @if($totalDiscount > 0)
    <tr class="disc-row">
        <td class="label">Discount</td>
        <td class="val">− {{ $currency }} {{ number_format($totalDiscount, 2) }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">TAX {{ rtrim(rtrim(number_format((float)$taxPercent, 2), '0'), '.') }}%</td>
        <td class="val">{{ $currency }} {{ number_format((float)$taxAmount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Total</td>
        <td class="val">{{ $currency }} {{ number_format((float)$discounts['net_before_tax'] + (float)$taxAmount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Withholding Tax {{ rtrim(rtrim(number_format((float)$withholdingPercent, 2), '0'), '.') }}%</td>
        <td class="val">{{ $currency }} {{ number_format((float)$withholdingAmount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Paid</td>
        <td class="val">{{ $currency }} {{ number_format((float)$paid, 2) }}</td>
    </tr>
    <tr class="grand">
        <td class="label">Balance Due</td>
        <td class="val">{{ $currency }} {{ number_format((float)$balanceDue, 2) }}</td>
    </tr>
</table>

@if(!empty($notes))
<div class="section">Notes</div>
<div class="notes {{ $notesIsArabic ? 'ar' : '' }}">{!! nl2br(e($notes)) !!}</div>
@endif

<table class="sign-table">
    <tr>
        <td>
            <div class="sig-box">
                <div class="sig-img">
                    @if(!empty($signatureDataUri))
                        <img src="{{ $signatureDataUri }}" alt="company signature" width="200" style="width:200px;height:auto;max-height:210px;">
                    @endif
                </div>
            </div>
            <div class="sig-line">Company signature</div>
            <div class="sig-company" style="margin-top:6px;">{{ $companyLegalName }}</div>
        </td>
        <td>
            <div class="sig-box">
                <div class="sig-img">&nbsp;</div>
            </div>
            <div class="sig-line">Client's signature</div>
            @if(!empty($clientName) && $clientName !== '—')
                <div class="sig-name {{ $clientNameIsArabic ? 'ar' : '' }}">{{ $clientName }}</div>
            @else
                <div class="sig-name">&nbsp;</div>
            @endif
        </td>
    </tr>
</table>

<div class="page-foot">- Invoice {{ $invoice->number }} - {{ $dateLabel }}</div>
</div>

</body>
</html>
