<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payment = App\Models\ProjectPayment::find(1) ?: App\Models\ProjectPayment::orderByDesc('id')->first();
if (!$payment) {
    fwrite(STDERR, "no payments\n");
    exit(1);
}
$project = App\Models\Project::find($payment->project_id);
$ctrl = app(App\Http\Controllers\ProjectFinanceController::class);
$ref = new ReflectionMethod($ctrl, 'paymentReceiptViewData');
$ref->setAccessible(true);
$data = $ref->invoke($ctrl, $project, $payment, false);
$fontRef = new ReflectionMethod($ctrl, 'receiptFontEmbedCss');
$fontRef->setAccessible(true);
$data['fontEmbedCss'] = $fontRef->invoke($ctrl);
$data['forBrowserPdf'] = true;
$html = view('payments.receipt-html', $data)->render();

echo 'chrome available: ' . (App\Support\BrowserPdf::available() ? 'yes' : 'no') . "\n";

$t = microtime(true);
$pdf = App\Support\BrowserPdf::render(
    $html,
    1200,
    ['width' => 8.27, 'height' => 5.83, 'landscape' => true]
);
$ms = (int) ((microtime(true) - $t) * 1000);

if ($pdf === null) {
    fwrite(STDERR, "render FAILED after {$ms}ms\n");
    exit(1);
}

$out = __DIR__ . '/receipt-browser.pdf';
file_put_contents($out, $pdf);
printf("ok ms=%d bytes=%d pages=%d -> %s\n", $ms, strlen($pdf), preg_match_all('#/Type\s*/Page[^s]#', $pdf), $out);
