<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$note = App\Models\ProjectDeliveryNote::orderByDesc('id')->first();
if (!$note) {
    fwrite(STDERR, "no delivery notes in database\n");
    exit(1);
}
$project = App\Models\Project::find($note->project_id);

$ctrl = app(App\Http\Controllers\ProjectFinanceController::class);
$ref = new ReflectionMethod($ctrl, 'deliveryNoteViewData');
$ref->setAccessible(true);
$data = $ref->invoke($ctrl, $project, $note);

echo "project={$project->id} note={$note->id} number={$data['noteNumber']}\n";
echo "clientName=[{$data['clientName']}]\n";
echo "items=" . count($data['items']) . "\n";

$html = view('delivery-notes.html', $data)->render();
file_put_contents(__DIR__ . '/dn-preview.html', $html);
echo 'html bytes=' . strlen($html) . "\n";
echo 'chrome available: ' . (App\Support\BrowserPdf::available() ? 'yes' : 'no') . "\n";

$pdf = App\Support\BrowserPdf::render($html);
if ($pdf === null) {
    fwrite(STDERR, "browser render FAILED\n");
    exit(1);
}

$out = __DIR__ . '/dn-browser.pdf';
file_put_contents($out, $pdf);
printf("pdf bytes=%d pages=%d -> %s\n", strlen($pdf), preg_match_all('#/Type\s*/Page[^s]#', $pdf), $out);
