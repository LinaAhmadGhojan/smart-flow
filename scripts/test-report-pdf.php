<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'Chrome available: ' . (App\Support\BrowserPdf::available() ? 'yes' : 'no') . "\n";

$report = App\Models\Report::first();
if (!$report) {
    fwrite(STDERR, "NO_REPORT\n");
    exit(1);
}

$ctrl = app(App\Http\Controllers\ReportController::class);
$resp = $ctrl->pdf($report);
$body = $resp instanceof Illuminate\Http\Response ? $resp->getContent() : $resp->getContent();
$out = __DIR__ . '/../storage/app/test-report-browser.pdf';
file_put_contents($out, $body);
echo "OK " . strlen($body) . " bytes -> $out\n";
