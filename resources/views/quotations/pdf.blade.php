<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $docTitle }} {{ $doc->number }}</title>
<style>
    @font-face {
        font-family: 'ArabicReport';
        src: url('{{ $arabicFontUrl }}') format('truetype');
    }
    @page { margin: 36px 40px 50px 40px; }
    body { font-family: DejaVu Sans, 'ArabicReport', sans-serif; color: #333; font-size: 11px; line-height: 1.4; }
    .ar { font-family: 'ArabicReport', DejaVu Sans, sans-serif; direction: rtl; unicode-bidi: bidi-override; }
    .top { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .top td { vertical-align: top; border: none; padding: 0; }
    .left { width: 34%; }
    .center { width: 32%; text-align: center; }
    .right { width: 34%; text-align: right; }
    .center img { width: 72px; height: auto; max-height: 72px; object-fit: contain; }
    .country { margin: 0 0 2px; color: #444; }
    .trn { margin: 0; color: #777; font-size: 10px; }
    .doc-title { font-size: 28px; font-weight: bold; margin: 0 0 8px; color: #222; }
    .meta { margin: 2px 0; }
    .for { margin: 12px 0 14px; font-size: 12px; }
    .items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .items th {
        background: #f3f3f3; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;
        text-align: left; padding: 7px 6px; font-size: 11px; color: #444;
    }
    .items td { border-bottom: 1px solid #eee; padding: 8px 6px; vertical-align: top; }
    .items .thumb { width: 126px; height: 126px; object-fit: cover; border: 1px solid #ddd; border-radius: 6px; }
    .items .thumb-empty { width: 126px; height: 126px; background: #f5f5f5; border: 1px solid #eee; border-radius: 6px; }
    .totals { width: 45%; margin-left: auto; border-collapse: collapse; }
    .totals td { padding: 4px 6px; }
    .totals .label { text-align: right; color: #555; }
    .totals .val { text-align: right; white-space: nowrap; width: 120px; }
    .totals .grand td {
        border-top: 2px solid #2177cf; border-bottom: 2px solid #2177cf;
        font-size: 14px; font-weight: bold; padding-top: 8px; padding-bottom: 8px; color: #2177cf;
    }
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
    .summary-page { page-break-before: always; break-before: page; }
    .comments { white-space: pre-wrap; }
    .foot-company { margin-top: 36px; font-size: 9px; color: #666; text-align: center; width: 100%; }
    .sign-table { width: 100%; border-collapse: collapse; margin-top: 36px; table-layout: fixed; }
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
    .sig-label {
        border-top: 1.5px solid #222; padding-top: 8px; margin: 0 auto;
        font-size: 11px; color: #333; font-weight: bold; width: 85%;
    }
    .sig-name {
        font-size: 11px; color: #444; margin-top: 6px; line-height: 1.35;
        width: 85%; margin-left: auto; margin-right: auto; min-height: 28px;
    }
</style>
</head>
<body>

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
                <img src="{{ $logoDataUri }}" alt="logo" width="72" height="72" style="width:72px;height:72px;">
            @endif
        </td>
        <td class="right">
            <p class="doc-title">{{ $docTitle }}</p>
            <p class="meta"><strong>{{ $docTitle }} No:</strong> {{ $doc->number }}</p>
            <p class="meta"><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($doc->date)->format('d/m/Y') }}</p>
        </td>
    </tr>
</table>

<div class="for">
    <strong>For:</strong>
    <span class="{{ $clientNameIsArabic ? 'ar' : '' }}">{{ $clientName }}</span>
</div>

<table class="items">
    <thead>
        <tr>
            <th style="width:8%">Code</th>
            <th style="width:24%">Description</th>
            <th style="width:16%">Image</th>
            <th style="width:7%" class="num">Quantity</th>
            <th style="width:10%" class="num">Rate</th>
            <th style="width:9%" class="num">Item Disc.</th>
            <th style="width:11%" class="num">Global Disc.</th>
            <th style="width:11%" class="num">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        @if(!empty($item['is_section']))
        <tr class="section-row">
            <td colspan="8" style="text-align:center;" class="{{ $item['descriptionIsArabic'] ? 'ar' : '' }}">{{ $item['description'] }}</td>
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
            <td class="num">{{ $doc->currency }} {{ number_format((float)$item['rate'], 2) }}</td>
            <td class="num disc">{{ $item['discount_label'] ?? '—' }}</td>
            <td class="num disc">{{ $item['global_discount_label'] ?? '—' }}</td>
            <td class="num">
                @if(!empty($item['discount_amount']) && (float)$item['discount_amount'] > 0)
                    <span class="was-price">{{ $doc->currency }} {{ number_format((float)($item['line_subtotal'] ?? $item['amount']), 2) }}</span>
                @elseif(!empty($item['global_discount_share']) && (float)$item['global_discount_share'] > 0)
                    <span class="was-price">{{ $doc->currency }} {{ number_format((float)$item['amount'], 2) }}</span>
                @endif
                {{ $doc->currency }} {{ number_format((float)($item['final_amount'] ?? $item['amount']), 2) }}
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>

<div class="summary-page">
<table class="totals">
    <tr>
        <td class="label">Parts Subtotal</td>
        <td class="val">{{ $doc->currency }} {{ number_format((float)$discounts['gross_subtotal'], 2) }}</td>
    </tr>
    @if((float)$discounts['line_discount_total'] > 0)
    <tr class="disc-row">
        <td class="label">Item Discount</td>
        <td class="val disc-val">− {{ $doc->currency }} {{ number_format((float)$discounts['line_discount_total'], 2) }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">Subtotal</td>
        <td class="val">{{ $doc->currency }} {{ number_format((float)$discounts['subtotal'], 2) }}</td>
    </tr>
    @if((float)$discounts['global_discount'] > 0)
    <tr class="disc-row">
        <td class="label">Discount{{ $doc->discount_type === 'percent' ? ' ' . rtrim(rtrim(number_format((float)$doc->discount_value, 2), '0'), '.') . '%' : '' }}</td>
        <td class="val disc-val">− {{ $doc->currency }} {{ number_format((float)$discounts['global_discount'], 2) }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">TAX {{ rtrim(rtrim(number_format((float)$doc->tax_percent, 2), '0'), '.') }}%</td>
        <td class="val">{{ $doc->currency }} {{ number_format((float)$doc->tax_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Total</td>
        <td class="val">{{ $doc->currency }} {{ number_format((float)$discounts['net_before_tax'] + (float)$doc->tax_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Withholding Tax {{ rtrim(rtrim(number_format((float)$doc->withholding_tax_percent, 2), '0'), '.') }}%</td>
        <td class="val">{{ $doc->currency }} {{ number_format((float)$doc->withholding_tax_amount, 2) }}</td>
    </tr>
    <tr class="grand">
        <td class="label">Total</td>
        <td class="val">{{ $doc->currency }} {{ number_format((float)$doc->total, 2) }}</td>
    </tr>
</table>

@if(!empty($comments))
<div class="section">Comments</div>
<div class="comments {{ $commentsIsArabic ? 'ar' : '' }}">{!! nl2br(e($comments)) !!}</div>
@endif

<div class="foot-company">{{ $companyLegalName }}</div>

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
            <div class="sig-label">Company signature</div>
            @if(!empty($signatureName))
                <div class="sig-name">{{ $signatureName }}</div>
            @else
                <div class="sig-name">&nbsp;</div>
            @endif
        </td>
        <td>
            <div class="sig-box">
                <div class="sig-img">&nbsp;</div>
            </div>
            <div class="sig-label">Client's signature</div>
            @if(!empty($clientName) && $clientName !== '—')
                <div class="sig-name {{ $clientNameIsArabic ? 'ar' : '' }}">{{ $clientName }}</div>
            @else
                <div class="sig-name">&nbsp;</div>
            @endif
        </td>
    </tr>
</table>
</div>

</body>
</html>
