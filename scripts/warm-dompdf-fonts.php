<?php

use App\Support\DompdfFontCache;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

DompdfFontCache::ensureReady();

$warm = [
    'ArabicReport' => resource_path('fonts/NotoNaskhArabic-Regular.ttf'),
    'ArReg' => resource_path('fonts/Cairo-Regular.ttf'),
    'ArBold' => resource_path('fonts/Cairo-Bold.ttf'),
    'Tajawal' => resource_path('fonts/Tajawal-Regular.ttf'),
];

foreach ($warm as $family => $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "Missing: {$path}\n");
        continue;
    }
    $url = 'file:///' . str_replace('\\', '/', $path);
    $html = <<<HTML
<html><head><meta charset="utf-8"><style>
@font-face { font-family: '{$family}'; src: url('{$url}') format('truetype'); }
body { font-family: '{$family}', sans-serif; font-size: 14px; }
</style></head><body>اختبار PDF — {$family}</body></html>
HTML;
    Pdf::loadHTML($html)->output();
    echo "Warmed {$family}\n";
}

echo "Font cache ready in storage/fonts\n";
