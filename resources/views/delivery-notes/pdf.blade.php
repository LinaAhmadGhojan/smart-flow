<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $note->number }}</title>
<style>
    @font-face {
        font-family: 'ArabicReport';
        src: url('{{ $arabicFontUrl }}') format('truetype');
    }
    @page { margin: 24px 28px 28px 28px; }
    body {
        font-family: 'ArabicReport', DejaVu Sans, sans-serif;
        color: #0f172a;
        font-size: 10.5px;
        line-height: 1.5;
        margin: 0;
        direction: rtl;
    }
    .ar { font-family: 'ArabicReport', DejaVu Sans, sans-serif; direction: rtl; unicode-bidi: bidi-override; }

    .sheet { width: 100%; }

    /* Brand top bar */
    .brand-bar {
        height: 5px;
        background: #12327d;
        margin: 0 0 18px 0;
    }

    .top {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    .top td { border: none; vertical-align: middle; padding: 0; }
    .top-brand { width: 42%; text-align: right; }
    .top-meta { width: 58%; text-align: left; direction: ltr; }
    .eyebrow {
        font-size: 8px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: #1d4f91;
        font-weight: bold;
        margin: 0 0 4px;
        font-family: DejaVu Sans, sans-serif;
    }
    .title {
        font-size: 17px;
        font-weight: bold;
        color: #12327d;
        margin: 0;
        line-height: 1.35;
    }
    .meta-line {
        font-size: 9.5px;
        color: #475569;
        margin: 0;
        line-height: 1.55;
        font-family: DejaVu Sans, sans-serif;
    }
    .meta-line strong { color: #12327d; }

    .divider {
        border: none;
        border-top: 1px solid #dbe3f0;
        margin: 0 0 16px;
    }

    .info {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    .info td {
        width: 50%;
        vertical-align: top;
        border: none;
        padding: 0 12px 0 0;
    }
    .info td + td {
        padding: 0 0 0 12px;
        border-right: 1px solid #e8eef6;
    }
    .lbl {
        font-size: 8.5px;
        color: #1d4f91;
        margin: 0 0 5px;
        letter-spacing: 0.02em;
    }
    .val {
        font-size: 12px;
        font-weight: bold;
        color: #0f172a;
        margin: 0;
        line-height: 1.4;
    }
    .sub {
        font-size: 9.5px;
        color: #64748b;
        margin: 4px 0 0;
    }

    .items {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 16px;
    }
    .items thead th {
        background: #f3f6fb;
        color: #12327d;
        font-size: 8.5px;
        font-weight: bold;
        padding: 8px 8px;
        border-top: 1px solid #dbe3f0;
        border-bottom: 1px solid #dbe3f0;
        text-align: center;
    }
    .items thead th.desc { text-align: right; }
    .items td {
        padding: 10px 8px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-size: 10.5px;
    }
    .items .num { width: 36px; text-align: center; color: #64748b; }
    .items .desc { text-align: right; }
    .items .qty {
        width: 52px;
        text-align: center;
        font-weight: bold;
        color: #12327d;
        direction: ltr;
        font-family: DejaVu Sans, sans-serif;
    }

    .notes {
        background: #f8fafc;
        border: 1px solid #e8eef6;
        padding: 10px 12px;
        margin-bottom: 22px;
    }
    .notes .lbl { margin-bottom: 4px; }
    .notes .txt {
        margin: 0;
        font-size: 10.5px;
        color: #334155;
        white-space: pre-wrap;
    }

    .signs {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }
    .signs td {
        width: 50%;
        vertical-align: bottom;
        border: none;
        padding: 0 14px;
        text-align: center;
    }
    .sig-stamp {
        min-height: 120px;
        margin-bottom: 6px;
        text-align: center;
    }
    .sig-stamp img {
        width: 160px;
        max-width: 100%;
        height: auto;
    }
    .sig-caption {
        font-size: 9.5px;
        color: #12327d;
        font-weight: bold;
        margin: 0 0 8px;
    }
    .sig-line {
        border-top: 1.2px solid #94a3b8;
        width: 82%;
        margin: 0 auto;
    }
    .sig-name {
        font-size: 9.5px;
        color: #64748b;
        margin-top: 7px;
    }

    .foot {
        margin-top: 20px;
        text-align: center;
        font-size: 8px;
        color: #94a3b8;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-family: DejaVu Sans, sans-serif;
    }
    .foot-line {
        width: 48px;
        height: 2px;
        background: #1d4f91;
        margin: 0 auto 8px;
    }
</style>
</head>
<body>
<div class="sheet">

    <div class="brand-bar"></div>

    <table class="top">
        <tr>
            <td class="top-brand">
                <p class="eyebrow">Delivery Note</p>
                <p class="title {{ $titleIsArabic ? 'ar' : '' }}">{{ $titleShaped ?: $note->number }}</p>
            </td>
            <td class="top-meta">
                <p class="meta-line"><strong>No</strong> &nbsp; {{ $note->number }}</p>
                <p class="meta-line"><strong>Date</strong> &nbsp; {{ $dateLabel }}</p>
            </td>
        </tr>
    </table>

    <hr class="divider">

    <table class="info">
        <tr>
            <td>
                <p class="lbl ar">{{ $lblClient }}</p>
                <p class="val {{ $clientIsArabic ? 'ar' : '' }}">{{ $clientName }}</p>
            </td>
            <td>
                <p class="lbl ar">{{ $lblProject }}</p>
                <p class="val {{ $projectTitleIsArabic ? 'ar' : '' }}">{{ $projectTitle }}</p>
                @if(!empty($projectLocation))
                    <p class="sub {{ $projectLocationIsArabic ? 'ar' : '' }}">{{ $projectLocation }}</p>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="num">#</th>
                <th class="desc ar">{{ $lblDesc }}</th>
                <th class="qty">Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td class="desc {{ !empty($item['isArabic']) ? 'ar' : '' }}">{{ $item['description'] }}</td>
                <td class="qty">{{ $item['quantity'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center;color:#94a3b8;padding:14px;">—</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($notesShaped))
    <div class="notes">
        <p class="lbl ar">{{ $lblNotes }}</p>
        <p class="txt {{ $notesIsArabic ? 'ar' : '' }}">{{ $notesShaped }}</p>
    </div>
    @endif

    <table class="signs">
        <tr>
            <td>
                <div class="sig-stamp">
                    @if(!empty($signatureDataUri))
                        <img src="{{ $signatureDataUri }}" alt="signature" width="160" style="width:160px;height:auto;max-height:120px;">
                    @endif
                </div>
                <p class="sig-caption"><span class="ar">{{ $lblDeliveredAr }}</span> / Delivered By</p>
                <div class="sig-line"></div>
                <p class="sig-name {{ $deliveredByIsArabic ? 'ar' : '' }}">{{ $deliveredByShaped }}</p>
            </td>
            <td>
                <div class="sig-stamp">&nbsp;</div>
                <p class="sig-caption"><span class="ar">{{ $lblReceivedAr }}</span> / Received By</p>
                <div class="sig-line"></div>
                <p class="sig-name {{ $receivedByIsArabic ? 'ar' : '' }}">{{ $receivedByShaped }}</p>
            </td>
        </tr>
    </table>

    <div class="foot">
        <div class="foot-line"></div>
        {{ $companyShort }}
    </div>

</div>
</body>
</html>
