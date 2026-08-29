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
    @page { size: A5 landscape; margin: 4mm 6mm 3mm 6mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        margin: 0;
        padding: 0;
        color: #1a437f;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.3;
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

    .header { width: 100%; border-collapse: collapse; margin-bottom: 6px; page-break-inside: avoid; }
    .header td { vertical-align: top; border: none; padding: 0; }
    .logo-cell { width: 36%; text-align: left; }
    .logo-cell img { height: 38px; width: auto; max-width: 110px; display: block; }
    .logo-name { font-size: 13px; color: #1a437f; margin-top: 3px; text-align: left; line-height: 1.15; }
    .logo-tag { display: none; }
    .title-cell { width: 64%; text-align: right; vertical-align: top; }
    .doc-title { font-size: 20px; color: #1a437f; margin: 0 0 6px 0; text-align: right; line-height: 1.1; }
    .meta { width: 78%; margin-left: auto; border-collapse: collapse; }
    .meta td { padding: 3px 0; vertical-align: bottom; border: none; font-size: 11.5px; color: #1a437f; }
    .meta .lab { white-space: nowrap; width: 1%; padding-left: 10px; text-align: right; direction: rtl; }
    .meta .val {
        border-bottom: 1.5px dotted #6b8fc4;
        text-align: center;
        padding: 0 6px 2px;
        font-size: 11.5px;
        color: #0f3266;
        min-width: 110px;
    }

    .sheet {
        border: 1.8px solid #1a437f;
        border-radius: 12px;
        padding: 10px 14px 8px;
        page-break-inside: avoid;
    }

    .fline { width: 100%; border-collapse: collapse; margin: 4px 0; }
    .fline td { padding: 4px 0; vertical-align: bottom; border: none; }
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
        border-bottom: 1.5px dotted #7a9cc8;
        padding: 0 6px 3px;
        text-align: right;
        color: #0f3266;
        font-size: 12px;
        min-height: 16px;
        line-height: 1.35;
    }

    .pay-wrap { margin: 10px 0 6px; position: relative; page-break-inside: avoid; }
    .pay-tab-row { text-align: center; height: 0; position: relative; z-index: 2; }
    .pay-tab {
        display: inline-block;
        background: #1a437f;
        color: #fff;
        font-size: 11px;
        padding: 3px 16px;
        border-radius: 3px;
        position: relative;
        top: 9px;
    }
    .pay-box {
        border: 1.5px solid #1a437f;
        border-radius: 8px;
        padding: 16px 0 8px;
    }
    .pay-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pay-grid td {
        width: 33.33%;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-left: 1px solid #b8cce8;
        padding: 4px 6px;
        color: #1a437f;
    }
    .pay-grid td:first-child { border-left: none; }
    .pay-icon { height: 26px; margin: 0 auto 3px; display: block; }
    .pay-label-row { margin-top: 2px; white-space: nowrap; }
    .chk {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 1.5px solid #1a437f;
        text-align: center;
        line-height: 10px;
        font-size: 8px;
        font-family: DejaVu Sans, sans-serif;
        margin: 0 0 0 5px;
        vertical-align: middle;
        color: #1a437f;
    }
    .chk.on { background: #1a437f; color: #fff; }
    .pay-lbl { font-size: 11.5px; vertical-align: middle; }

    .grid {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0 8px;
        table-layout: fixed;
        page-break-inside: avoid !important;
    }
    .grid tr { page-break-inside: avoid !important; page-break-after: avoid !important; }
    .grid th {
        background: #1a437f;
        color: #fff;
        font-size: 11.5px;
        padding: 7px 5px;
        text-align: center;
        border: 1px solid #1a437f;
        font-weight: bold;
    }
    .grid td {
        border: 1px solid #b8cce8;
        padding: 8px 5px;
        text-align: center;
        vertical-align: middle;
        height: 24px;
        font-size: 11px;
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

    .signs { width: 100%; border-collapse: collapse; margin-top: 6px; page-break-inside: avoid; }
    .signs td { width: 50%; border: none; vertical-align: bottom; padding: 0 12px; text-align: center; }
    .sig-lab { font-size: 11px; color: #1a437f; margin-bottom: 3px; }
    .sig-line { border-bottom: 1px dotted #7a9cc8; height: 18px; }
    .sig-line img { height: 16px; width: auto; max-width: 75px; vertical-align: bottom; }

    .thanks {
        font-size: 12.5px;
        color: #1a437f;
        padding: 8px 0 3px;
        text-align: center;
        page-break-inside: avoid;
    }

    .contact {
        text-align: center;
        direction: ltr;
        margin: 1px 0 0;
        font-size: 9px;
        color: #1a437f;
        font-family: DejaVu Sans, 'ArReg', sans-serif;
        line-height: 1.4;
        white-space: nowrap;
        page-break-inside: avoid;
    }
    .contact img {
        height: 9px;
        width: 9px;
        vertical-align: middle;
        margin-right: 3px;
    }
    .contact span { margin: 0 9px; white-space: nowrap; }

    .footer-bar {
        margin-top: 6px;
        height: 8px;
        background: #1a437f;
        border-radius: 0 0 2px 2px;
        page-break-inside: avoid;
    }
    .wave-wrap { display: none; }
</style>
</head>
<body>
@include('payments._receipt-body')
</body>
</html>
