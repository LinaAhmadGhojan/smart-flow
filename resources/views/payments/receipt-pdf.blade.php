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
    @page { size: A5 landscape; margin: 3mm 5mm 2mm 5mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        margin: 0;
        padding: 0;
        color: #1a437f;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        font-size: 10px;
        line-height: 1.25;
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

    .header { width: 100%; border-collapse: collapse; margin-bottom: 3px; page-break-inside: avoid; }
    .header td { vertical-align: top; border: none; padding: 0; }
    .logo-cell { width: 36%; text-align: left; }
    .logo-cell img { height: 32px; width: auto; max-width: 95px; display: block; }
    .logo-name { font-size: 12px; color: #1a437f; margin-top: 2px; text-align: left; line-height: 1.1; }
    .logo-tag { display: none; }
    .title-cell { width: 64%; text-align: right; vertical-align: top; }
    .doc-title { font-size: 17px; color: #1a437f; margin: 0 0 3px 0; text-align: right; line-height: 1.1; }
    .meta { width: 75%; margin-left: auto; border-collapse: collapse; }
    .meta td { padding: 1px 0; vertical-align: bottom; border: none; font-size: 10px; color: #1a437f; }
    .meta .lab { white-space: nowrap; width: 1%; padding-left: 8px; text-align: right; direction: rtl; }
    .meta .val {
        border-bottom: 1px dotted #6b8fc4;
        text-align: center;
        padding: 0 4px 1px;
        font-size: 10px;
        color: #1a437f;
        min-width: 90px;
    }

    .sheet {
        border: 1.5px solid #1a437f;
        border-radius: 8px;
        padding: 5px 9px 4px;
        page-break-inside: avoid;
    }

    .fline { width: 100%; border-collapse: collapse; margin: 1px 0; }
    .fline td { padding: 2px 0; vertical-align: bottom; border: none; }
    .fline .lab {
        white-space: nowrap;
        width: 1%;
        padding-left: 8px;
        text-align: right;
        font-size: 10px;
        color: #1a437f;
        direction: rtl;
    }
    .fline .val {
        border-bottom: 1px dotted #7a9cc8;
        padding: 0 3px 1px;
        text-align: right;
        color: #1a437f;
        font-size: 10px;
        min-height: 12px;
    }

    .pay-wrap { margin: 5px 0 3px; position: relative; page-break-inside: avoid; }
    .pay-tab-row { text-align: center; height: 0; position: relative; z-index: 2; }
    .pay-tab {
        display: inline-block;
        background: #1a437f;
        color: #fff;
        font-size: 9px;
        padding: 2px 14px;
        border-radius: 3px;
        position: relative;
        top: 7px;
    }
    .pay-box {
        border: 1.5px solid #1a437f;
        border-radius: 5px;
        padding: 12px 0 4px;
    }
    .pay-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pay-grid td {
        width: 33.33%;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-left: 1px solid #b8cce8;
        padding: 1px 3px 3px;
        color: #1a437f;
    }
    .pay-grid td:first-child { border-left: none; }
    .pay-icon { height: 20px; margin: 0 auto 1px; display: block; }
    .pay-label-row { margin-top: 1px; white-space: nowrap; }
    .chk {
        display: inline-block;
        width: 10px;
        height: 10px;
        border: 1.4px solid #1a437f;
        text-align: center;
        line-height: 8px;
        font-size: 7px;
        font-family: DejaVu Sans, sans-serif;
        margin: 0 0 0 3px;
        vertical-align: middle;
        color: #1a437f;
    }
    .chk.on { background: #1a437f; color: #fff; }
    .pay-lbl { font-size: 9px; vertical-align: middle; }

    .grid {
        width: 100%;
        border-collapse: collapse;
        margin: 2px 0 3px;
        table-layout: fixed;
        page-break-inside: avoid !important;
    }
    .grid tr { page-break-inside: avoid !important; page-break-after: avoid !important; }
    .grid th {
        background: #1a437f;
        color: #fff;
        font-size: 9px;
        padding: 4px 2px;
        text-align: center;
        border: 1px solid #1a437f;
        font-weight: bold;
    }
    .grid td {
        border: 1px solid #b8cce8;
        padding: 5px 2px;
        text-align: center;
        vertical-align: middle;
        height: 18px;
        font-size: 9px;
        color: #1a437f;
        background: #fff;
    }
    .grid td.amt-cell { background: #e8f4fb; font-weight: bold; }
    .cell-dots {
        display: inline-block;
        width: 85%;
        border-bottom: 1px dotted #7a9cc8;
        height: 10px;
    }

    .signs { width: 100%; border-collapse: collapse; margin-top: 1px; page-break-inside: avoid; }
    .signs td { width: 33%; border: none; vertical-align: bottom; padding: 0 5px; text-align: center; }
    .sig-lab { font-size: 9px; color: #1a437f; margin-bottom: 1px; }
    .sig-line { border-bottom: 1px dotted #7a9cc8; height: 14px; }
    .sig-line img { height: 14px; width: auto; max-width: 60px; vertical-align: bottom; }
    .thanks { font-size: 10px; color: #1a437f; padding: 2px 0; text-align: center; }

    .contact {
        text-align: center;
        direction: ltr;
        margin-top: 2px;
        font-size: 8px;
        color: #1a437f;
        font-family: DejaVu Sans, 'ArReg', sans-serif;
        line-height: 1.3;
        white-space: nowrap;
        page-break-inside: avoid;
    }
    .contact img {
        height: 8px;
        width: 8px;
        vertical-align: middle;
        margin-left: 2px;
        margin-right: 1px;
    }
    .contact span { margin: 0 6px; white-space: nowrap; }

    .wave-wrap {
        margin-top: 1px;
        line-height: 0;
        max-height: 8px;
        overflow: hidden;
        page-break-inside: avoid;
    }
    .wave-wrap img { width: 100%; height: auto; display: block; }
</style>
</head>
<body>
@include('payments._receipt-body')
</body>
</html>
