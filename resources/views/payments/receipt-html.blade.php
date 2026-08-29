@php
    $webLayout = empty($forPdf);
    $fixedPage = !empty($forBrowserPdf);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="{{ $webLayout ? 'rtl' : 'ltr' }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta charset="utf-8"/>
@if($fixedPage || !empty($fontEmbedCss ?? null))
@if(!empty($fontEmbedCss ?? null))
<style>{!! $fontEmbedCss !!}</style>
@endif
@elseif($webLayout)
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet"/>
@else
<style>
    @font-face {
        font-family: 'Cairo';
        src: url('{{ $fontRegularUrl }}') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'Cairo';
        src: url('{{ $fontBoldUrl }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
</style>
@endif
<title>{{ $receiptNumber }}</title>
<style>
    @if($fixedPage)
    @page { size: A5 landscape; margin: 0; }
    @elseif(!empty($forPdf))
    @page { size: A5 landscape; margin: 5mm 7mm 4mm 7mm; }
    @endif

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        background: #fff;
        font-family: 'Cairo', 'CairoFallback', 'Segoe UI', 'DejaVu Sans', sans-serif;
        color: #1a437f;
        @if($webLayout && !$fixedPage)
        -webkit-font-smoothing: antialiased;
        @endif
        @if($fixedPage)
        width: 210mm;
        height: 148mm;
        margin: 0;
        padding: 0;
        overflow: hidden;
        @endif
    }
    .page-wrap {
        @if($fixedPage || !empty($forPdf))
        padding: 0;
        background: #fff;
        display: block;
        @else
        min-height: 100vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 12px;
        background: #f4f7fb;
        @endif
        @if($fixedPage)
        width: 210mm;
        height: 148mm;
        margin: 0;
        overflow: hidden;
        @endif
    }
    .receipt-page {
        @if($fixedPage)
        width: 210mm;
        max-width: 210mm;
        height: 148mm;
        min-height: 148mm;
        padding: 18px 22px 0;
        display: flex;
        flex-direction: column;
        @elseif(!empty($forPdf))
        width: 100%;
        max-width: none;
        min-height: auto;
        padding: 0;
        @else
        width: 794px;
        max-width: 100%;
        min-height: 559px;
        padding: 18px 22px 0;
        display: flex;
        flex-direction: column;
        @endif
        background: #fff;
        font-size: 11px;
        line-height: 1.35;
        overflow: hidden;
    }
    .ar {
        font-family: 'Cairo', 'CairoFallback', 'Segoe UI', 'DejaVu Sans', sans-serif;
        font-weight: 400;
        @if(!empty($forPdf))
        direction: ltr;
        unicode-bidi: bidi-override;
        @endif
    }
    .arb {
        font-family: 'Cairo', 'CairoFallback', 'Segoe UI', 'DejaVu Sans', sans-serif;
        font-weight: 700;
        @if(!empty($forPdf))
        direction: ltr;
        unicode-bidi: bidi-override;
        @endif
    }
    .en {
        font-family: 'DejaVu Sans', sans-serif;
        font-weight: 400;
        direction: ltr;
        unicode-bidi: embed;
    }

    .header { width: 100%; border-collapse: collapse; margin-bottom: 8px; direction: ltr; }
    .header td { vertical-align: top; border: none; padding: 0; }
    .logo-cell { width: 38%; text-align: left; }
    .logo-cell img { height: 44px; width: auto; max-width: 120px; display: block; }
    .logo-name {
        font-size: 15px;
        font-weight: 700;
        color: #1a437f;
        margin-top: 4px;
        text-align: left;
        line-height: 1.2;
    }
    .title-cell { width: 62%; text-align: right; vertical-align: top; direction: rtl; }
    .doc-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a437f;
        margin-bottom: 8px;
        line-height: 1.1;
    }
    .meta { width: 78%; margin-right: 0; margin-left: auto; border-collapse: collapse; direction: rtl; }
    .meta td { padding: 4px 0; vertical-align: bottom; border: none; font-size: 12px; color: #1a437f; }
    .meta .lab { white-space: nowrap; width: 1%; padding-left: 12px; text-align: right; font-weight: 700; }
    .meta .val {
        border-bottom: 1.5px dotted #8faed0;
        text-align: center;
        padding: 0 8px 4px;
        font-size: 12px;
        font-weight: 600;
        color: #0f3266;
        min-width: 120px;
    }

    .sheet {
        border: 1.8px solid #1a437f;
        border-radius: 14px;
        padding: 12px 16px 10px;
        margin-top: 4px;
    }

    .fline { width: 100%; border-collapse: collapse; margin: 6px 0; }
    .fline td { padding: 5px 0; vertical-align: bottom; border: none; }
    .fline .lab {
        white-space: nowrap;
        width: 1%;
        padding-left: 12px;
        text-align: right;
        font-size: 13px;
        font-weight: 700;
        color: #1a437f;
    }
    .fline .val {
        border-bottom: 1.5px dotted #8faed0;
        padding: 0 8px 5px;
        text-align: right;
        color: #0f3266;
        font-size: 13px;
        font-weight: 600;
        min-height: 20px;
        width: 99%;
        line-height: 1.45;
        letter-spacing: 0.01em;
    }
    .fline-empty .val { min-height: 16px; }

    .pay-wrap { margin: 16px 0 10px; }
    .pay-box {
        position: relative;
        border: 1.5px solid #1a437f;
        border-radius: 8px;
        padding: 28px 0 12px;
        background: #fff;
    }
    .pay-tab {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #1a437f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 18px;
        border-radius: 3px;
        white-space: nowrap;
        line-height: 1.3;
        z-index: 2;
    }
    .pay-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pay-grid td {
        width: 33.33%;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-left: 1px solid #c5d8ef;
        padding: 8px 8px;
        color: #1a437f;
        background: #fff;
    }
    .pay-grid td:last-child { border-left: none; }
    .pay-item {
        display: inline-block;
        white-space: nowrap;
        line-height: 1;
        vertical-align: middle;
    }
    .pay-icon {
        height: 34px;
        width: 38px;
        object-fit: contain;
        vertical-align: middle;
        display: inline-block;
    }
    .chk {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 1.5px solid #1a437f;
        border-radius: 2px;
        text-align: center;
        line-height: 13px;
        font-size: 10px;
        font-weight: 700;
        font-family: 'DejaVu Sans', sans-serif;
        background: #fff;
        vertical-align: middle;
    }
    .chk.on {
        background: #1a437f;
        border-color: #1a437f;
        color: #fff;
    }
    .pay-lbl {
        font-size: 13px;
        font-weight: 700;
        vertical-align: middle;
        color: #1a437f;
        margin: 0 8px;
    }

    .grid { width: 100%; border-collapse: collapse; margin: 8px 0 10px; table-layout: fixed; }
    .grid th {
        background: #1a437f;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 6px;
        text-align: center;
        border: 1px solid #1a437f;
    }
    .grid td {
        border: 1px solid #b8cce8;
        padding: 10px 6px;
        text-align: center;
        vertical-align: middle;
        height: 32px;
        font-size: 11px;
        color: #1a437f;
        background: #fff;
    }
    .grid td.amt-cell { background: #e8f4fb; font-weight: 700; }
    .amt-num { font-weight: 700; margin-left: 4px; }
    .amt-cur { font-size: 10px; font-weight: 700; }
    .cell-dots {
        display: inline-block;
        width: 90%;
        border-bottom: 1px dotted #7a9cc8;
        height: 14px;
    }

    .signs { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .signs td { width: 33%; border: none; vertical-align: bottom; padding: 0 10px; text-align: center; }
    .sig-lab { font-size: 11.5px; font-weight: 700; margin-bottom: 4px; color: #1a437f; }
    .sig-line { border-bottom: 1px dotted #7a9cc8; height: 24px; }
    .sig-line img { height: 22px; width: auto; max-width: 90px; vertical-align: bottom; }
    .thanks {
        font-size: 13px;
        font-weight: 700;
        color: #1a437f;
        padding: 8px 0 4px;
        text-align: center;
    }

    .contact {
        text-align: center;
        direction: ltr;
        margin: 2px 0 0;
        padding: 0 12px 2px;
        font-size: 9px;
        font-weight: 600;
        color: #1a437f;
        line-height: 1.5;
    }
    .contact img {
        height: 11px;
        width: 11px;
        vertical-align: middle;
        margin-right: 4px;
    }
    .contact > span { margin: 0 12px; white-space: nowrap; display: inline-block; }

    .wave-wrap {
        margin: auto -22px 0;
        line-height: 0;
        height: 52px;
        overflow: hidden;
    }
    .wave-wrap img {
        width: 100%;
        height: 52px;
        display: block;
        object-fit: fill;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    @if($webLayout && !$fixedPage)
    @media print {
        @page { size: A5 landscape; margin: 0; }

        html, body, .page-wrap {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .page-wrap { display: block !important; min-height: auto !important; }

        /* 210mm x 148mm equals the 794x559px screen design, so the printed
           sheet reflows exactly like the browser view and stays on one page. */
        .receipt-page {
            width: 210mm !important;
            max-width: 210mm !important;
            height: 148mm !important;
            min-height: 148mm !important;
            padding: 18px 22px 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            box-shadow: none !important;
            display: flex !important;
            flex-direction: column !important;
            break-after: avoid;
            break-inside: avoid;
        }
        .wave-wrap {
            margin: auto -22px 0 !important;
            height: 52px !important;
            overflow: hidden !important;
        }
        .wave-wrap img {
            width: 100% !important;
            height: 52px !important;
            display: block !important;
            object-fit: fill !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    @endif

    @if($fixedPage)
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    @endif
</style>
</head>
<body>
<div class="page-wrap">
<div class="receipt-page">

@include('payments._receipt-body-html')

</div>
</div>
</body>
</html>
