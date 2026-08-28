<?php

return [
    /*
    | Headless Chromium/Chrome/Edge binary used to print HTML views to PDF.
    | Leave null to auto-detect the usual install locations for the platform.
    */
    'chrome_binary' => env('CHROME_BINARY'),

    'chrome_timeout' => (int) env('CHROME_TIMEOUT', 60),
];
