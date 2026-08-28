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
    @page { margin: 5mm 7mm 4mm 7mm; }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        padding: 0;
        color: #1a437f;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.35;
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
    .logo-cell { width: 36%; text-align: left; }
    .logo-cell img { height: 40px; width: auto; max-width: 110px; display: block; }
    .logo-name {
        font-size: 14px;
        color: #1a437f;
        margin-top: 3px;
        text-align: left;
        line-height: 1.2;
    }
    .logo-tag {
        font-size: 8px;
        color: #2a5a9a;
        margin-top: 2px;
        text-align: left;
        line-height: 1.25;
        font-weight: normal;
    }
    .title-cell { width: 64%; text-align: right; vertical-align: top; }
    .doc-title {
        font-size: 22px;
        color: #1a437f;
        margin: 0 0 6px 0;
        text-align: right;
        line-height: 1.1;
    }
    .meta { width: 72%; margin-left: auto; border-collapse: collapse; }
    .meta td { padding: 3px 0; vertical-align: bottom; border: none; font-size: 12px; color: #1a437f; }
    .meta .lab { white-space: nowrap; width: 1%; padding-left: 10px; text-align: right; direction: rtl; }
    .meta .val {
        border-bottom: 1px dotted #6b8fc4;
        text-align: center;
        padding: 0 6px 2px;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        font-size: 11.5px;
        color: #1a437f;
        min-width: 110px;
    }

    .sheet {
        border: 1.8px solid #1a437f;
        border-radius: 14px;
        padding: 10px 14px 8px;
        margin-bottom: 0;
    }

    .fline { width: 100%; border-collapse: collapse; margin: 5px 0; }
    .fline td { padding: 4px 0; vertical-align: bottom; border: none; }
    .fline .lab {
        white-space: nowrap;
        width: 1%;
        padding-left: 10px;
        text-align: right;
        font-size: 13px;
        color: #1a437f;
        direction: rtl;
    }
    .fline .val {
        border-bottom: 1px dotted #7a9cc8;
        padding: 0 4px 2px;
        text-align: right;
        color: #1a437f;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        font-size: 12px;
        font-weight: normal;
        min-height: 16px;
    }

    .pay-wrap { margin: 10px 0 6px; position: relative; }
    .pay-tab-row { text-align: center; height: 0; position: relative; z-index: 2; }
    .pay-tab {
        display: inline-block;
        background: #1a437f;
        color: #fff;
        font-size: 12px;
        padding: 4px 22px;
        border-radius: 4px;
        position: relative;
        top: 10px;
    }
    .pay-box {
        border: 1.8px solid #1a437f;
        border-radius: 8px;
        padding: 18px 0 10px;
        margin-top: 0;
    }
    .pay-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pay-grid td {
        width: 33.33%;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-left: 1px solid #b8cce8;
        padding: 4px 6px 6px;
        color: #1a437f;
    }
    .pay-grid td:first-child { border-left: none; }
    .pay-icon { height: 28px; margin: 0 auto 4px; display: block; }
    .pay-label-row { margin-top: 2px; white-space: nowrap; }
    .chk {
        display: inline-block;
        width: 13px;
        height: 13px;
        border: 1.8px solid #1a437f;
        text-align: center;
        line-height: 11px;
        font-size: 9px;
        font-family: DejaVu Sans, sans-serif;
        margin: 0 0 0 5px;
        vertical-align: middle;
        color: #1a437f;
    }
    .chk.on { background: #1a437f; color: #fff; }
    .pay-lbl { font-size: 12px; vertical-align: middle; }

    .grid { width: 100%; border-collapse: collapse; margin: 5px 0 8px; table-layout: fixed; }
    .grid th {
        background: #1a437f;
        color: #fff;
        font-size: 12px;
        padding: 7px 5px;
        text-align: center;
        border: 1px solid #1a437f;
        font-weight: bold;
    }
    .grid td {
        border: 1px solid #b8cce8;
        padding: 9px 5px;
        text-align: center;
        vertical-align: middle;
        height: 28px;
        font-size: 11px;
        color: #1a437f;
        background: #fff;
    }
    .grid td.amt-cell {
        background: #e8f4fb;
        font-weight: bold;
    }
    .cell-dots {
        display: inline-block;
        width: 88%;
        border-bottom: 1px dotted #7a9cc8;
        height: 14px;
    }

    .signs { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .signs td { width: 33%; border: none; vertical-align: bottom; padding: 0 8px; text-align: center; }
    .sig-lab { font-size: 11.5px; color: #1a437f; margin-bottom: 3px; }
    .sig-line {
        border-bottom: 1px dotted #7a9cc8;
        height: 22px;
    }
    .sig-line img { height: 20px; width: auto; max-width: 85px; vertical-align: bottom; }
    .thanks {
        font-size: 12px;
        color: #1a437f;
        padding: 10px 0 4px;
        text-align: center;
    }

    .contact {
        text-align: center;
        margin-top: 4px;
        font-size: 8.5px;
        color: #2a5a9a;
        font-family: 'ArReg', DejaVu Sans, sans-serif;
        line-height: 1.5;
    }
    .contact img {
        height: 9px;
        width: 9px;
        vertical-align: middle;
        margin-left: 3px;
        margin-right: 1px;
    }
    .contact span { margin: 0 7px; white-space: nowrap; }

    .wave-wrap { margin-top: 4px; line-height: 0; max-height: 16px; overflow: hidden; }
    .wave-wrap img { width: 100%; height: auto; display: block; }
</style>
</head>
<body>

@include('payments._receipt-body')

</body>
</html>
