<!DOCTYPE html>
<html lang="ar">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $receiptNumber }}</title>
<style>
    @font-face {
        font-family: 'ArReg';
        src: url('{{ $fontRegularUrl }}') format('truetype');
        font-weight: normal;
    }
    @font-face {
        font-family: 'ArBold';
        src: url('{{ $fontBoldUrl }}') format('truetype');
        font-weight: bold;
    }
    @page { size: A5 landscape; margin: 4mm 6mm 0 6mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        margin: 0;
        padding: 0;
        color: #1a437f;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.35;
        width: 100%;
    }
    .ar {
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        direction: ltr;
        unicode-bidi: bidi-override;
        font-weight: normal;
    }
    .arb {
        font-family: 'ArBold', 'ArReg', DejaVu Sans, sans-serif;
        direction: ltr;
        unicode-bidi: bidi-override;
        font-weight: bold;
    }
    .en { font-family: DejaVu Sans, sans-serif; direction: ltr; font-weight: normal; }

    .header { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
    .header td { vertical-align: top; border: none; padding: 0; }
    .logo-cell { width: 38%; text-align: left; }
    .logo-cell img { height: 36px; width: auto; max-width: 105px; display: block; }
    .logo-name { font-size: 13px; color: #1a437f; margin-top: 2px; text-align: left; line-height: 1.15; }
    .title-cell { width: 62%; text-align: right; vertical-align: top; }
    .doc-title { font-size: 20px; color: #1a437f; margin: 0 0 4px 0; text-align: right; line-height: 1.1; }
    .meta { width: 80%; margin-left: auto; border-collapse: collapse; }
    .meta td { padding: 3px 0; vertical-align: bottom; border: none; font-size: 12px; color: #1a437f; }
    .meta .lab { white-space: nowrap; width: 1%; padding-left: 10px; text-align: right; direction: rtl; }
    .meta .val {
        border-bottom: 1.5px dotted #8faed0;
        text-align: center;
        padding: 0 6px 2px;
        font-size: 12px;
        color: #0f3266;
        min-width: 115px;
    }

    .sheet {
        border: 1.8px solid #1a437f;
        border-radius: 12px;
        padding: 8px 12px 6px;
        page-break-inside: avoid;
    }

    .fline { width: 100%; border-collapse: collapse; margin: 2px 0; }
    .fline td { padding: 3px 0; vertical-align: bottom; border: none; }
    .fline .lab {
        white-space: nowrap;
        width: 1%;
        padding-left: 10px;
        text-align: right;
        font-size: 12.5px;
        color: #1a437f;
        direction: rtl;
    }
    .fline .val {
        border-bottom: 1.5px dotted #8faed0;
        padding: 0 6px 3px;
        text-align: right;
        color: #0f3266;
        font-size: 12px;
        line-height: 1.4;
    }

    .pay-wrap { margin: 8px 0 5px; }
    .pay-box {
        border: 1.5px solid #1a437f;
        border-radius: 8px;
        padding: 0 0 5px;
    }
    .pay-tab-row { text-align: center; margin: -1px 0 5px; }
    .pay-tab {
        display: inline-block;
        background: #1a437f;
        color: #fff;
        font-size: 11px;
        padding: 3px 18px;
        border-radius: 0 0 4px 4px;
        letter-spacing: 0;
    }
    .pay-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pay-grid > tbody > tr > td {
        width: 33.33%;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-left: 1px solid #c5d8ef;
        padding: 3px 2px;
        color: #1a437f;
    }
    .pay-grid > tbody > tr > td:first-child { border-left: none; }

    .pay-item { margin: 0 auto; border-collapse: collapse; }
    .pay-item td { border: none; padding: 0 2px; vertical-align: middle; }
    .pi-ico { width: 1%; }
    .pi-lbl { width: auto; white-space: nowrap; }
    .pi-chk { width: 1%; }
    .pay-icon { height: 26px; width: auto; display: block; }
    .chk {
        display: inline-block;
        width: 13px;
        height: 13px;
        border: 1.5px solid #1a437f;
        text-align: center;
        line-height: 11px;
        font-size: 9px;
        font-family: DejaVu Sans, sans-serif;
        vertical-align: middle;
        color: #1a437f;
        background: #fff;
    }
    .chk.on { background: #1a437f; color: #fff; }
    .pay-lbl { font-size: 12px; vertical-align: middle; white-space: nowrap; }

    .grid {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0 5px;
        table-layout: fixed;
    }
    .grid th {
        background: #1a437f;
        color: #fff;
        font-size: 11px;
        padding: 5px 4px;
        text-align: center;
        border: 1px solid #1a437f;
        font-weight: bold;
    }
    .grid td {
        border: 1px solid #b8cce8;
        padding: 6px 4px;
        text-align: center;
        vertical-align: middle;
        height: 20px;
        font-size: 10.5px;
        color: #1a437f;
        background: #fff;
    }
    .grid td.amt-cell { background: #e8f4fb; font-weight: bold; }
    .cell-dots {
        display: inline-block;
        width: 88%;
        border-bottom: 1px dotted #7a9cc8;
        height: 12px;
    }

    .signs { width: 100%; border-collapse: collapse; margin-top: 2px; }
    .signs td { width: 50%; border: none; vertical-align: bottom; padding: 0 12px; text-align: center; }
    .sig-lab { font-size: 10.5px; color: #1a437f; margin-bottom: 1px; }
    .sig-line { border-bottom: 1px dotted #7a9cc8; height: 14px; }

    .thanks {
        font-size: 12px;
        color: #1a437f;
        padding: 5px 0 2px;
        text-align: center;
    }

    .contact {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 2px;
        table-layout: fixed;
    }
    .contact td {
        border: none;
        padding: 2px 8px;
        text-align: center;
        vertical-align: middle;
        font-size: 10px;
        color: #1a437f;
        font-family: DejaVu Sans, 'ArReg', sans-serif;
        white-space: nowrap;
    }
    .contact img {
        height: 15px;
        width: 15px;
        vertical-align: middle;
        margin-right: 5px;
    }

    .wave-wrap {
        margin: 2px 0 0;
        line-height: 0;
        height: 28px;
        overflow: hidden;
        page-break-inside: avoid;
    }
    .wave-wrap img {
        width: 100%;
        height: 28px;
        display: block;
    }
</style>
</head>
<body>
@include('payments._receipt-body')
</body>
</html>
