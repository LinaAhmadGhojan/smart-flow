<?php

return [
    /*
    | Headless Chromium/Chrome/Edge binary used to print HTML views to PDF.
    | Leave null to auto-detect the usual install locations for the platform.
    |
    | Receipt PDFs use Dompdf A5 by default (reliable on shared hosting).
    | Set RECEIPT_PDF_BROWSER=true only if CDP paper-size print works on the server.
    | Never rely on Chrome CLI --print-to-pdf for receipts: it defaults to A4.
    */
    'chrome_binary' => env('CHROME_BINARY'),

    'chrome_timeout' => (int) env('CHROME_TIMEOUT', 60),

    'receipt_use_browser' => (bool) env('RECEIPT_PDF_BROWSER', false),
];
