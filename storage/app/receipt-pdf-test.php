<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payment = App\Models\ProjectPayment::orderByDesc('id')->first();
if (!$payment) {
    fwrite(STDERR, "no payments in database\n");
    exit(1);
}
$project = App\Models\Project::find($payment->project_id);

$ctrl = app(App\Http\Controllers\ProjectFinanceController::class);
$ref = new ReflectionMethod($ctrl, 'paymentReceiptViewData');
$ref->setAccessible(true);
$data = $ref->invoke($ctrl, $project, $payment, false);

echo "project={$project->id} payment={$payment->id} receipt={$data['receiptNumber']}\n";
echo "clientName=[{$data['clientName']}]\n";
echo "amountWords=[{$data['amountWords']}]\n";

$fontRef = new ReflectionMethod($ctrl, 'receiptFontEmbedCss');
$fontRef->setAccessible(true);
$data['fontEmbedCss'] = $fontRef->invoke($ctrl);

$html = view('payments.receipt-html', $data)->render();
file_put_contents(__DIR__ . '/receipt-preview.html', $html);

echo 'chrome available: ' . (App\Support\BrowserPdf::available() ? 'yes' : 'no') . "\n";

$pdf = App\Support\BrowserPdf::render($html);
if ($pdf === null) {
    fwrite(STDERR, "browser render FAILED\n");
    exit(1);
}

$out = __DIR__ . '/receipt-browser.pdf';
file_put_contents($out, $pdf);
printf("pdf bytes=%d pages=%d -> %s\n", strlen($pdf), preg_match_all('#/Type\s*/Page[^s]#', $pdf), $out);
