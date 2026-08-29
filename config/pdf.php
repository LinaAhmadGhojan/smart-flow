<?php

return [
    /*
    | Headless Chromium/Chrome/Edge binary used to print HTML views to PDF.
    | Leave null to auto-detect the usual install locations for the platform.
    |
    | On Linux hosting (required for receipt PDFs to match the web view):
    |   sudo apt install chromium-browser   # Debian/Ubuntu
    |   CHROME_BINARY=/usr/bin/chromium-browser
    */
    'chrome_binary' => env('CHROME_BINARY'),

    'chrome_timeout' => (int) env('CHROME_TIMEOUT', 60),
];
