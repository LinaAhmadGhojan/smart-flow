<style>
    @font-face {
        font-family: 'ArabicReport';
        src: url('{{ $arabicFontUrl }}') format('truetype');
    }
    @page { margin: 14px 16px 20px 16px; }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, 'ArabicReport', sans-serif;
        color: #1a437f;
        font-size: 10.5px;
        line-height: 1.45;
        margin: 0;
    }
    .ar {
        font-family: 'ArabicReport', DejaVu Sans, sans-serif;
        direction: rtl;
        unicode-bidi: bidi-override;
    }
    .en { direction: ltr; unicode-bidi: embed; }

    .hdr { width: 100%; border-collapse: collapse; margin-bottom: 10px; direction: ltr; }
    .hdr td { vertical-align: middle; border: none; padding: 0; }
    .hdr-brand { width: 56%; padding-right: 10px; }
    .hdr-inner { width: 100%; border-collapse: collapse; }
    .hdr-inner td { border: none; vertical-align: middle; padding: 0; }
    .logo-cell { width: 175px; padding-right: 10px; }
    .logo { width: 175px; max-width: 175px; max-height: 110px; height: auto; display: block; }
    .brand-name { font-size: 20px; font-weight: bold; color: #1a437f; line-height: 1.15; margin-bottom: 3px; text-align: left; }
    .brand-row { font-size: 11px; color: #1a437f; font-weight: bold; margin: 2px 0; white-space: nowrap; }
    .brand-row img { width: 14px; height: 14px; vertical-align: middle; margin-right: 5px; }
    .trn-line { font-size: 9px; color: #2f5f9e; margin-top: 3px; font-weight: bold; }

    .hdr-doc { width: 44%; border-left: 1.5px solid #8faed0; padding-left: 12px; direction: rtl; text-align: center; }
    .doc-ar { font-size: 22px; font-weight: bold; color: #1a437f; line-height: 1.05; }
    .doc-en { font-size: 11px; font-weight: bold; letter-spacing: 0.08em; color: #2f5f9e; margin-top: 2px; }
    .meta-pair { width: 100%; border-collapse: collapse; margin-top: 5px; direction: rtl; }
    .meta-pair td { border: none; padding: 2px 0; vertical-align: middle; }
    .meta-lab { font-size: 11px; font-weight: bold; color: #1a437f; white-space: nowrap; padding-left: 8px; text-align: right; width: 42%; }
    .meta-box {
        border: 1.5px solid #1a437f; border-radius: 7px; padding: 4px 12px;
        font-weight: bold; font-size: 11px; color: #1a437f; text-align: center; background: #fff;
    }
    .meta-extra { width: 100%; border-collapse: collapse; margin-top: 6px; direction: rtl; border: 1.3px solid #1a437f; }
    .meta-extra td {
        border-left: 1px solid #8faed0; border-right: 1px solid #8faed0;
        border-top: none; border-bottom: 1px solid #8faed0;
        padding: 4px 8px; font-size: 10px; height: 24px; vertical-align: middle;
    }
    .meta-extra tr:first-child td { border-top: 1px solid #8faed0; }
    .meta-extra .lab { background: #1a437f; color: #fff; font-weight: bold; text-align: right; border-bottom-color: #9ec4ea; }
    .meta-extra .val { text-align: center; font-weight: bold; color: #1a437f; background: #fff; }

    .client-card {
        width: 100%; border-collapse: collapse; margin-bottom: 10px; direction: rtl;
        border: 1.3px solid #1a437f; border-radius: 8px; overflow: hidden;
    }
    .client-card td { border: none; padding: 0; vertical-align: top; }
    .client-h {
        background: #1a437f; color: #fff; font-weight: bold; font-size: 11px;
        padding: 6px 10px; text-align: right;
    }
    .client-b { padding: 7px 10px; font-size: 11px; font-weight: bold; color: #1a437f; }

    .grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .grid th {
        background: #1a437f; color: #fff; font-size: 10px; font-weight: bold;
        padding: 6px 5px; text-align: center; border: 1px solid #1a437f;
    }
    .grid td {
        border: 1px solid #8faed0; padding: 5px 5px; text-align: center;
        font-size: 10px; color: #1a437f; vertical-align: top; background: #fff;
    }
    .grid tr:nth-child(even) td { background: #f3f7fc; }
    .grid .desc { text-align: right; padding-right: 7px; }
    .grid .num { text-align: right; direction: ltr; white-space: nowrap; }
    .grid .section-row td {
        background: #1a437f !important; color: #fff !important; font-weight: bold;
        font-size: 11px; padding: 7px 8px; text-align: center; border-color: #1a437f;
    }
    .grid .thumb { width: 88px; height: 88px; object-fit: cover; border: 1px solid #8faed0; border-radius: 5px; }
    .grid .thumb-empty { width: 88px; height: 88px; background: #fff; border: 1px solid #8faed0; border-radius: 5px; margin: 0 auto; }

    .summary-page { page-break-before: always; break-before: page; padding-top: 4px; }

    .totals-wrap { width: 52%; margin-left: auto; margin-bottom: 12px; border: 1.3px solid #1a437f; border-radius: 8px; overflow: hidden; }
    .totals-h { background: #1a437f; color: #fff; font-weight: bold; font-size: 11px; padding: 6px 10px; text-align: center; }
    .tot { width: 100%; border-collapse: collapse; }
    .tot td { border: 1px solid #8faed0; padding: 5px 8px; font-size: 10px; }
    .tot .k { font-weight: bold; text-align: right; width: 58%; background: #fff; color: #1a437f; }
    .tot .v { text-align: center; direction: ltr; font-weight: bold; color: #1a437f; white-space: nowrap; }
    .tot tr.disc-row .k, .tot tr.disc-row .v { color: #c0392b; }
    .tot tr.grand td { background: #1a437f; color: #fff; font-weight: bold; font-size: 11px; }

    .notes-h { font-weight: bold; font-size: 11px; color: #1a437f; margin: 10px 0 4px; }
    .notes-b { white-space: pre-wrap; font-size: 10px; color: #1a437f; font-weight: bold; border: 1px solid #8faed0; border-radius: 6px; padding: 8px 10px; background: #fff; }

    .signs-wrap { background: #e8f0fa; border: 1.3px solid #1a437f; border-radius: 8px; margin-top: 14px; overflow: hidden; }
    .signs { width: 100%; border-collapse: collapse; }
    .signs td { width: 50%; vertical-align: top; text-align: center; padding: 8px 10px 10px; border-left: 1px solid #8faed0; }
    .signs td:first-child { border-left: none; }
    .sig-ttl { font-size: 10px; font-weight: bold; color: #1a437f; margin-bottom: 6px; }
    .stamp-box {
        border: 1.3px dashed #8faed0; height: 70px; border-radius: 4px; margin: 0 auto 6px;
        background: #fff; width: 88%; text-align: center;
    }
    .stamp-box img { max-width: 120px; max-height: 64px; margin-top: 4px; object-fit: contain; }
    .sig-line { border-bottom: 1.2px dotted #8faed0; min-height: 16px; margin: 4px auto; text-align: right; font-size: 9.5px; font-weight: bold; color: #1a437f; width: 88%; padding: 0 4px; }
    .sig-name { font-size: 9.5px; color: #1a437f; font-weight: bold; margin-top: 4px; min-height: 14px; }

    .footer { margin-top: 16px; position: relative; height: 52px; overflow: hidden; }
    .thanks-bar {
        position: absolute; top: 4px; left: 50%; transform: translateX(-50%);
        background: #fff; padding: 0 8px; font-size: 11px; font-weight: bold; color: #1a437f; z-index: 2; white-space: nowrap;
    }
    .wave-wrap { position: absolute; left: 0; right: 0; bottom: 0; height: 46px; line-height: 0; }
    .wave-wrap img { width: 100%; height: 46px; display: block; object-fit: fill; }
</style>
