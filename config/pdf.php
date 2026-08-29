<?php

return [
    /*
    | Headless Chromium/Chrome/Edge binary used to print HTML views to PDF.
    | Leave null to auto-detect the usual install locations for the platform.
    |
    | Receipt export tries Chrome CDP automatically when a browser is found
    | (matches the on-screen HTML: wave, Arabic, A5). Dompdf is the fallback
    | for shared hosting without Chrome. RECEIPT_PDF_BROWSER=true forces the
    | browser path; =false still allows auto-detect when a binary exists.
    | Never rely on Chrome CLI --print-to-pdf for receipts: it defaults to A4.
    */
    'chrome_binary' => env('CHROME_BINARY'),

    'chrome_timeout' => (int) env('CHROME_TIMEOUT', 60),

    'receipt_use_browser' => (bool) env('RECEIPT_PDF_BROWSER', false),
];
