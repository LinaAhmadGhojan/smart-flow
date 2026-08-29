<?php

namespace App\Console\Commands;

use App\Support\BrowserPdf;
use Illuminate\Console\Command;

/**
 * Render HTML → PDF via Chrome CDP in a fresh PHP CLI process.
 * Needed on Windows because spawning Chrome from `artisan serve` often fails
 * Winsock DevTools binding (0x277A / Cannot start http server for devtools).
 */
class ChromeRenderPdfCommand extends Command
{
    protected $signature = 'pdf:chrome-render
        {html : Absolute path to the HTML input file}
        {out : Absolute path for the PDF output file}
        {--width=8.27 : Paper width in inches}
        {--height=5.83 : Paper height in inches}
        {--portrait : Portrait instead of landscape}
        {--wait=1200 : Settle wait in ms}';

    protected $description = 'Render an HTML file to PDF with headless Chrome (A5-capable CDP)';

    public function handle(): int
    {
        $htmlPath = (string) $this->argument('html');
        $outPath = (string) $this->argument('out');

        if (!is_file($htmlPath)) {
            $this->error('HTML file not found: ' . $htmlPath);

            return self::FAILURE;
        }

        $html = (string) file_get_contents($htmlPath);
        $pdf = BrowserPdf::renderDirectCdp(
            $html,
            (int) $this->option('wait'),
            [
                'width' => (float) $this->option('width'),
                'height' => (float) $this->option('height'),
                'landscape' => ! (bool) $this->option('portrait'),
            ]
        );

        if ($pdf === null || strlen($pdf) < 1000) {
            $this->error('Chrome CDP render failed');

            return self::FAILURE;
        }

        if (file_put_contents($outPath, $pdf) === false) {
            $this->error('Could not write PDF: ' . $outPath);

            return self::FAILURE;
        }

        $this->info('ok ' . strlen($pdf));

        return self::SUCCESS;
    }
}
