<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@php
    $webLayout = empty($forPdf);
    $fixedPage = !empty($forBrowserPdf);
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet"/>
@if(!empty($fontEmbedCss ?? null))
<style>{!! $fontEmbedCss !!}</style>
@endif
@if($webLayout)
<meta name="viewport" content="width=device-width, initial-scale=1"/>
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
        padding: 12px 18px 78px;
        @else
        width: 794px;
        max-width: 100%;
        min-height: 1123px;
        padding: 12px 18px 78px;
        @endif
        background: #fff;
        border: none;
        border-radius: 0;
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
    /* Applied in-browser before PDF capture (same idea as delivery note). */
    body.report-capture .page-wrap {
        min-height: auto !important;
        display: block !important;
        background: #fff !important;
        padding: 0 !important;
    }
    body.report-capture .sheet {
        width: 794px !important;
        max-width: 794px !important;
        overflow: visible !important;
        margin: 0 auto;
        box-shadow: none !important;
        height: auto !important;
    }
    .en { direction: ltr; unicode-bidi: embed; }
    span.en, .brand-contact .en { display: inline-block; }

    .meta-extra {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: rtl;
        margin-top: 6px;
        border: 1.3px solid #1a437f;
    }
    .meta-extra col { width: 50%; }
    .meta-extra tr { height: 28px; }
    .meta-extra td.lab,
    .meta-extra td.val {
        border-left: 1px solid #8faed0;
        border-right: 1px solid #8faed0;
        border-top: none;
        border-bottom: 1px solid #8faed0;
        padding: 0 10px;
        font-size: 11px;
        vertical-align: middle;
        width: 50%;
        height: 28px;
        line-height: 28px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }
    .meta-extra tr:first-child td.lab,
    .meta-extra tr:first-child td.val {
        border-top: 1px solid #8faed0;
    }
    .meta-extra tr:last-child td.lab,
    .meta-extra tr:last-child td.val {
        border-bottom: 1px solid #8faed0;
    }
    .meta-extra .lab {
        background: #1a437f;
        font-weight: 700;
        text-align: right;
        color: #fff;
        border-bottom-color: #9ec4ea;
    }
    .meta-extra tr:not(:last-child) .lab {
        box-shadow: inset 0 -1px 0 #9ec4ea;
    }
    .meta-extra .val {
        text-align: center;
        font-weight: 700;
        color: #1a437f;
        background: #fff;
    }

    .header {
        width: 100%;
        border: none;
        margin-bottom: 12px;
        border-collapse: collapse;
        direction: ltr;
        table-layout: fixed;
        flex-shrink: 0;
    }
    .header td { vertical-align: middle; padding: 0; border: none; }
    .brand { width: 56%; padding: 4px 12px 4px 0; }
    .brand-inner { width: 100%; border-collapse: collapse; direction: ltr; }
    .brand-inner td { border: none; vertical-align: middle; padding: 0; }
    .logo-td { width: 175px; padding-right: 12px; }
    .logo-td img {
        width: 175px; height: auto; max-width: 175px; max-height: 120px;
        display: block; object-fit: contain;
    }
    .brand-text { direction: ltr; text-align: left; vertical-align: middle; }
    .brand-name {
        font-size: 24px; font-weight: 800; color: #1a437f; line-height: 1.15;
        margin-bottom: 4px; direction: rtl; text-align: left;
    }
    .brand-contact { margin-top: 2px; font-size: 12px; color: #1a437f; font-weight: 700; }
    .brand-contact .row {
        display: flex; align-items: center; justify-content: flex-start;
        gap: 8px; margin: 2px 0; white-space: nowrap; direction: ltr;
    }
    .brand-contact img { height: 15px; width: 15px; }

    .doc-cell {
        width: 44%;
        text-align: right;
        direction: rtl;
        padding: 4px 2px 4px 14px;
        border-left: 1.5px solid #8faed0;
        vertical-align: middle;
    }
    .doc-title-row {
        display: flex; align-items: center; gap: 10px;
        justify-content: center; margin-bottom: 8px;
    }
    .doc-title { text-align: center; }
    .doc-title .ar-title {
        font-size: 24px; font-weight: 800; color: #1a437f; line-height: 1.05;
    }
    .doc-title .en-title {
        font-size: 12px; font-weight: 800; letter-spacing: 0.08em;
        color: #2f5f9e; margin-top: 2px;
    }
    .doc-ico-circle {
        width: 44px; height: 44px; border-radius: 50%; background: #1a437f;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .doc-ico-circle svg { width: 24px; height: 24px; display: block; }
    .doc-meta { margin-top: 2px; }
    .meta-row {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        margin-bottom: 6px;
    }
    .meta-lab { font-size: 12px; font-weight: 800; white-space: nowrap; color: #1a437f; }
    .meta-box {
        border: 1.5px solid #1a437f;
        border-radius: 8px;
        padding: 4px 16px;
        text-align: center;
        font-weight: 800;
        font-size: 13px;
        direction: ltr;
        color: #1a437f;
        min-width: 132px;
        line-height: 1.3;
        background: #fff;
    }

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
        padding: 22px 12px 12px;
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
        font-size: 12px;
        font-weight: 800;
        padding: 5px 20px;
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
        background: #fff;
        border: 1.3px solid #1a437f;
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
        font-size: 15px;
        font-weight: 800;
        color: #1a437f;
        line-height: 1.2;
        white-space: nowrap;
    }

    .site-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        direction: rtl;
        border: 1.3px solid #1a437f;
    }
    .site-table col { width: 50%; }
    .site-table tr { height: 32px; }
    .site-table td.k,
    .site-table td.v {
        display: table-cell !important;
        border-left: 1px solid #8faed0;
        border-right: 1px solid #8faed0;
        border-top: none;
        border-bottom: 1px solid #8faed0;
        padding: 0 10px;
        font-size: 11px;
        vertical-align: middle;
        width: 50%;
        height: 32px;
        max-height: 32px;
        min-height: 32px;
        line-height: 32px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }
    .site-table tr:first-child td.k,
    .site-table tr:first-child td.v {
        border-top: 1px solid #8faed0;
    }
    .site-table tr:last-child td.k,
    .site-table tr:last-child td.v {
        border-bottom: 1px solid #8faed0;
    }
    .site-table .k {
        background: #1a437f;
        font-weight: 700;
        text-align: right;
        color: #fff;
        border-bottom-color: #9ec4ea;
    }
    .site-table tr:not(:last-child) .k {
        box-shadow: inset 0 -1px 0 #9ec4ea;
    }
    .site-table .v {
        text-align: center;
        color: #1a437f;
        font-weight: 700;
        background: #fff;
    }
    .site-table tr:nth-child(even) .v {
        background: #f3f7fc;
    }
    .site-table .v .en {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        line-height: 32px;
    }

    .bullets { margin: 0; padding: 0 14px 0 0; list-style: none; }
    .bullets li {
        position: relative; padding: 0 0 3px; font-size: 11px; line-height: 1.5;
        color: #1a437f; font-weight: 700;
    }
    .bullets li::before { content: "•"; color: #1a437f; font-weight: 700; position: absolute; right: -11px; }

    .signs-wrap {
        background: #e8f0fa;
        border-radius: 10px;
        margin: auto 0 8px;
        overflow: hidden;
        flex-shrink: 0;
        border: 1.3px solid #1a437f;
    }
    .signs { width: 100%; border-collapse: collapse; }
    .signs td {
        width: 33.33%; border: none; border-left: 1px solid #8faed0;
        vertical-align: top; padding: 8px 12px 10px; text-align: center;
    }
    .signs td:first-child { border-left: none; }
    .sig-head {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        margin-bottom: 8px;
    }
    .sig-ico { width: 15px; height: 15px; display: block; fill: none; stroke: #1a437f; stroke-width: 1.6; }
    .sig-ttl { font-size: 11px; font-weight: 700; color: #1a437f; }
    .sig-line {
        border-bottom: 1.2px dotted #8faed0; height: 18px; margin: 6px 0;
        text-align: right; font-size: 10px; padding: 0 4px; font-weight: 700; color: #1a437f;
    }
    .stamp-box {
        border: 1.3px dashed #8faed0; height: 100px; min-height: 100px; border-radius: 4px; margin-top: 6px;
        background: #fff;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
        padding: 4px 6px;
    }
    .stamp-box img {
        max-width: 100%; max-height: 92px; width: auto; height: auto;
        object-fit: contain; display: block;
    }

    .footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
        height: 72px;
        overflow: hidden;
    }
    .thanks-bar {
        position: absolute;
        top: 6px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
        color: #1a437f;
        white-space: nowrap;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .thanks-bar .line { width: 42px; height: 1.2px; background: #1a437f; }
    .thanks-bar .leaf { width: 16px; height: 16px; display: block; }
    .wave-wrap { position: absolute; left: 0; right: 0; bottom: 0; height: 52px; line-height: 0; }
    .wave-wrap img { width: 100%; height: 52px; display: block; object-fit: fill; }
    .circuit {
        position: absolute; left: 6px; bottom: 3px; width: 150px; height: 40px;
        opacity: 0.75; pointer-events: none;
    }

    @if($webLayout && !$fixedPage)
    @media print {
        @page { size: A4 portrait; margin: 0; }
        html, body, .page-wrap { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .page-wrap { display: block !important; }
        .sheet {
            width: 210mm !important; min-height: 297mm !important; height: 297mm !important;
            margin: 0 !important; padding: 12px 18px 78px !important;
            border: none !important; border-radius: 0 !important;
            display: flex !important; flex-direction: column !important;
        }
        .footer { height: 72px !important; }
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
