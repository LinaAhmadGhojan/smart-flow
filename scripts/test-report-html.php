<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$report = App\Models\Report::first();
if (!$report) {
    fwrite(STDERR, "NO_REPORT\n");
    exit(1);
}

$ctrl = app(App\Http\Controllers\ReportController::class);
$ref = new ReflectionMethod($ctrl, 'reportViewData');
$ref->setAccessible(true);
$data = $ref->invoke($ctrl, $report);
$out = __DIR__ . '/../storage/app/test-report.html';
file_put_contents($out, view('reports.report-html', $data)->render());
echo "HTML -> $out\n";
