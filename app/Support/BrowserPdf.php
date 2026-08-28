<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Print an HTML document to PDF with a headless Chromium browser.
 *
 * Unlike Dompdf this keeps Arabic shaping, bidi and web fonts identical to the
 * browser view, so a PDF is a pixel copy of the Blade page it came from.
 */
class BrowserPdf
{
    /** @return string|null Raw PDF bytes, or null when no browser is available. */
    public static function render(string $html, int $waitMs = 6000): ?string
    {
        $binary = self::binary();
        if ($binary === null) {
            return null;
        }

        $dir = storage_path('app/pdf-tmp');
        File::ensureDirectoryExists($dir);

        $id = bin2hex(random_bytes(8));
        $htmlFile = $dir . DIRECTORY_SEPARATOR . $id . '.html';
        $pdfFile = $dir . DIRECTORY_SEPARATOR . $id . '.pdf';
        $profileDir = $dir . DIRECTORY_SEPARATOR . 'profile-' . $id;

        file_put_contents($htmlFile, $html);

        try {
            $process = new Process([
                $binary,
                '--headless=new',
                '--disable-gpu',
                '--no-sandbox',
                '--no-first-run',
                '--no-default-browser-check',
                '--disable-extensions',
                '--disable-dev-shm-usage',
                '--hide-scrollbars',
                '--allow-file-access-from-files',
                '--user-data-dir=' . $profileDir,
                '--virtual-time-budget=' . $waitMs,
                '--no-pdf-header-footer',
                '--print-to-pdf=' . $pdfFile,
                self::fileUrl($htmlFile),
            ]);
            $process->setTimeout((float) config('pdf.chrome_timeout', 60));
            $process->run();

            if (!is_file($pdfFile) || filesize($pdfFile) < 1000) {
                Log::warning('BrowserPdf: chrome produced no output', [
                    'exit' => $process->getExitCode(),
                    'stderr' => mb_substr($process->getErrorOutput(), 0, 2000),
                ]);

                return null;
            }

            return (string) file_get_contents($pdfFile);
        } catch (ProcessFailedException|\Throwable $e) {
            Log::warning('BrowserPdf: ' . $e->getMessage());

            return null;
        } finally {
            File::delete($htmlFile);
            File::delete($pdfFile);
            File::deleteDirectory($profileDir);
        }
    }

    public static function available(): bool
    {
        return self::binary() !== null;
    }

    private static function binary(): ?string
    {
        $configured = config('pdf.chrome_binary');
        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        foreach (self::candidatePaths() as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /** @return string[] */
    private static function candidatePaths(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
            $programFilesX86 = getenv('ProgramFiles(x86)') ?: 'C:\\Program Files (x86)';
            $localAppData = getenv('LOCALAPPDATA') ?: '';

            return array_filter([
                $programFiles . '\\Google\\Chrome\\Application\\chrome.exe',
                $programFilesX86 . '\\Google\\Chrome\\Application\\chrome.exe',
                $localAppData !== '' ? $localAppData . '\\Google\\Chrome\\Application\\chrome.exe' : null,
                $programFiles . '\\Microsoft\\Edge\\Application\\msedge.exe',
                $programFilesX86 . '\\Microsoft\\Edge\\Application\\msedge.exe',
            ]);
        }

        return [
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/snap/bin/chromium',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        ];
    }

    private static function fileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        return 'file:///' . ltrim($normalized, '/');
    }
}
