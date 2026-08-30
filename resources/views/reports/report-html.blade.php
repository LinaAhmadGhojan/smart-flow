<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@php
    $webLayout = empty($forPdf);
    $fixedPage = !empty($forBrowserPdf);
@endphp
@if($fixedPage || !empty($fontEmbedCss ?? null))
@if(!empty($fontEmbedCss ?? null))
<style>{!! $fontEmbedCss !!}</style>
@endif
@elseif($webLayout)
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&family=Cairo:wght@400;700&display=swap" rel="stylesheet"/>
@endif
<title>تقرير زيارة موقع {{ $reportNo }}</title>
<style>
    @if($fixedPage)
    @page { size: A4 portrait; margin: 0; }
    @endif

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        background: #fff;
        color: #1a437f;
        font-family: 'Cairo', 'CairoFallback', 'Segoe UI', sans-serif;
        @if($webLayout && !$fixedPage)
        -webkit-font-smoothing: antialiased;
        @endif
        @if($fixedPage)
        width: 210mm;
        height: 297mm;
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
        justify-content: center;
        align-items: flex-start;
        background: #eef3fa;
        padding: 12px;
        @endif
        @if($fixedPage)
        width: 210mm;
        height: 297mm;
        margin: 0;
        overflow: hidden;
        @endif
    }
    .sheet {
        @if($fixedPage)
        width: 210mm;
        max-width: 210mm;
        height: 297mm;
        min-height: 297mm;
        padding: 8mm 9mm 14mm;
        @else
        width: 794px;
        max-width: 100%;
        min-height: 1123px;
        padding: 12px 14px 52px;
        @endif
        background: #fff;
        border: 1.5px solid #b8cce8;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        @if($fixedPage || !empty($forPdf))
        border: none;
        border-radius: 0;
        @endif
        @if(!empty($forPdf) && !$fixedPage)
        width: 100%;
        max-width: none;
        @endif
    }
    .en { direction: ltr; unicode-bidi: embed; }
    span.en, .contact-row .en { display: inline-block; }

    .meta {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: rtl;
    }
    .meta col { width: 50%; }
    .meta tr { height: 30px; }
    .meta td.lab,
    .meta td.val {
        display: table-cell !important;
        border: 1px solid #b8cce8;
        padding: 0 8px;
        font-size: 10px;
        vertical-align: middle;
        width: 50%;
        height: 30px;
        max-height: 30px;
        min-height: 30px;
        line-height: 30px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }
    .meta .lab {
        background: #f4f8fd;
        font-weight: 700;
        text-align: right;
    }
    .meta .val {
        text-align: center;
        font-weight: 600;
        color: #0f3266;
        background: #fff;
    }
    .meta .val .en {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        line-height: 30px;
    }

    .header {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: ltr;
        margin-bottom: 8px;
        flex-shrink: 0;
    }
    .header td { vertical-align: middle; border: none; padding: 0; }
    .brand { width: 54%; padding: 2px 10px 2px 0; }
    .brand-inner { width: 100%; border-collapse: collapse; direction: ltr; }
    .brand-inner td { border: none; vertical-align: middle; padding: 0; }
    .logo-td { width: 96px; padding-right: 10px; }
    .logo-td img { width: 90px; height: auto; max-height: 90px; display: block; }
    .brand-text { direction: ltr; text-align: left; vertical-align: middle; }
    .brand-name {
        font-size: 22px; font-weight: 700; color: #1a437f; line-height: 1.1;
        margin-bottom: 1px; direction: rtl; text-align: left;
    }
    .contact-row {
        font-size: 10px; color: #1a437f; line-height: 1.55;
        direction: ltr; text-align: left; white-space: nowrap;
    }
    .contact-row img { width: 12px; height: 12px; vertical-align: middle; margin-right: 5px; }

    .doc-cell {
        width: 46%;
        direction: rtl;
        vertical-align: top;
        padding: 0 0 0 12px;
        border-left: 1.5px solid #c9d9eb;
    }
    .doc-title-wrap { text-align: center; margin-bottom: 6px; }
    .doc-title-table { border-collapse: collapse; direction: ltr; margin: 0 auto; }
    .doc-title-table td { border: none; padding: 0 4px; vertical-align: middle; }
    .doc-title { font-size: 24px; font-weight: 700; color: #1a437f; line-height: 1; white-space: nowrap; }
    .doc-ico {
        width: 36px; height: 36px; border-radius: 50%; background: #1a437f;
        text-align: center; line-height: 36px;
    }
    .doc-ico svg { width: 18px; height: 18px; fill: #fff; vertical-align: middle; }

    .main-area { flex: 1; min-height: 0; }
    .body { width: 100%; border-collapse: collapse; table-layout: fixed; direction: ltr; }
    .body td { vertical-align: top; border: none; padding: 0; }
    .photos-col { width: 32%; padding-right: 10px; direction: rtl; vertical-align: top; }
    .content-col { width: 68%; padding-left: 6px; direction: rtl; }

    .photos-box {
        border: 1.5px solid #1a437f;
        border-radius: 12px;
        background: #fff;
        position: relative;
        padding: 20px 12px 12px;
        margin: 5px;
    }
    .photos-head-wrap {
        position: absolute;
        top: -11px;
        left: 0;
        right: 0;
        text-align: center;
        line-height: 0;
    }
    .photos-head {
        display: inline-block;
        background: #1a437f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 18px;
        border-radius: 4px;
        line-height: 1.35;
    }
    .photos-inner { width: 100%; }
    .photo-item {
        position: relative;
        margin: 0 0 10px;
        border-radius: 8px;
        overflow: hidden;
        height: 108px;
        background: #f4f8fd;
    }
    .photo-item:last-child { margin-bottom: 0; }
    .photo-item img { width: 100%; height: 108px; object-fit: cover; display: block; border-radius: 8px; }
    .photo-num {
        position: absolute; top: 5px; left: 5px; width: 20px; height: 20px;
        background: #1a437f; color: #fff; font-size: 10px; font-weight: 700;
        text-align: center; line-height: 20px; border-radius: 4px; z-index: 2;
    }

    .section { margin-bottom: 7px; }
    .section-head {
        display: flex;
        align-items: center;
        direction: rtl;
        margin-bottom: 4px;
    }
    .section-head-label {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
        background: #fff;
        padding: 0 3px;
        position: relative;
        z-index: 1;
    }
    .section-head-line {
        flex: 1;
        border-top: 1.5px solid #1a437f;
        height: 0;
        min-width: 0;
    }
    .section-ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }
    .section-ico svg { width: 20px; height: 20px; display: block; }
    .section-title {
        font-family: 'Tajawal', 'Cairo', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: #1a2b56;
        line-height: 1.2;
        white-space: nowrap;
    }

    .site-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: rtl;
    }
    .site-table col { width: 50%; }
    .site-table tr { height: 30px; }
    .site-table td.k,
    .site-table td.v {
        display: table-cell !important;
        border: 1px solid #b8cce8;
        padding: 0 8px;
        font-size: 10px;
        vertical-align: middle;
        width: 50%;
        height: 30px;
        max-height: 30px;
        min-height: 30px;
        line-height: 30px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }
    .site-table .k {
        background: #f4f8fd;
        font-weight: 700;
        text-align: right;
    }
    .site-table .v {
        text-align: center;
        color: #0f3266;
        font-weight: 600;
        background: #fff;
    }
    .site-table .v .en {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        line-height: 30px;
    }

    .bullets { margin: 0; padding: 0 14px 0 0; list-style: none; }
    .bullets li {
        position: relative; padding: 0 0 2px; font-size: 10px; line-height: 1.45;
        color: #0f3266; font-weight: 600;
    }
    .bullets li::before { content: "•"; color: #1a437f; font-weight: 700; position: absolute; right: -11px; }

    .signs-wrap {
        background: #eef4fb; border-radius: 8px; margin-top: 6px;
        overflow: hidden; flex-shrink: 0;
    }
    .signs { width: 100%; border-collapse: collapse; direction: rtl; }
    .signs td {
        width: 33.33%; vertical-align: top; text-align: center; border: none;
        padding: 7px 10px 8px; border-left: 1px dashed #8faed0;
    }
    .signs td:last-child { border-left: none; }
    .sig-head-table { margin: 0 auto 5px; border-collapse: collapse; }
    .sig-head-table td { border: none; padding: 0 3px; vertical-align: middle; }
    .sig-ico { width: 16px; height: 16px; display: block; fill: none; stroke: #1a437f; stroke-width: 1.6; }
    .sig-ttl { font-size: 10px; font-weight: 700; color: #1a437f; }
    .sig-line {
        border-bottom: 1px dotted #8faed0; min-height: 15px; margin: 4px 0;
        text-align: right; font-size: 9.5px; color: #1a437f; padding: 0 2px 1px; font-weight: 600;
    }
    .stamp-box {
        width: 88px; height: 54px; border: 1.5px dashed #8faed0;
        border-radius: 4px; margin: 6px auto 2px; background: #fafcff;
    }

    .footer {
        position: absolute; left: 0; right: 0; bottom: 0; height: 46px;
        background: #1a437f; overflow: hidden;
    }
    .footer-bar { width: 100%; height: 46px; border-collapse: collapse; direction: ltr; }
    .footer-bar td { border: none; vertical-align: middle; padding: 0; }
    .footer-side { width: 18%; height: 46px; overflow: hidden; }
    .footer-side img { width: 100%; height: 46px; object-fit: cover; opacity: 0.4; display: block; }
    .footer-center {
        text-align: center; color: #fff; font-size: 13px; font-weight: 700; white-space: nowrap;
    }
    .footer-bolt { width: 13px; height: 13px; fill: #fff; vertical-align: middle; margin: 0 8px; }

    @if($webLayout && !$fixedPage)
    @media print {
        @page { size: A4 portrait; margin: 0; }
        html, body, .page-wrap { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .page-wrap { display: block !important; }
        .sheet {
            width: 210mm !important; min-height: 297mm !important; height: 297mm !important;
            margin: 0 !important; padding: 8mm 9mm 14mm !important;
            border: 1.5px solid #b8cce8 !important; border-radius: 0 !important;
            display: flex !important; flex-direction: column !important;
        }
        .footer { height: 46px !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
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
@include('reports._report-body-html')
</div>
</body>
</html>
