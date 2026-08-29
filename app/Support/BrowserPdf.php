<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Print an HTML document to PDF with a headless Chromium browser.
 *
 * Keeps Arabic shaping / layout identical to the browser view.
 * When $paper is set, uses CDP Page.printToPDF for exact size (A5 etc).
 */
class BrowserPdf
{
    /**
     * @param  array{width?: float, height?: float, landscape?: bool}|null  $paper  Inches (A5 landscape = 8.27 × 5.83).
     * @return string|null Raw PDF bytes, or null when no browser is available.
     */
    public static function render(string $html, int $waitMs = 1500, ?array $paper = null): ?string
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
            if ($paper !== null) {
                $cdp = self::renderViaCdp($binary, $htmlFile, $profileDir, $waitMs, $paper);
                if ($cdp !== null) {
                    return $cdp;
                }
                Log::warning('BrowserPdf: CDP A5 print failed, falling back to CLI');
            }

            return self::renderViaCli($binary, $htmlFile, $pdfFile, $profileDir, $waitMs);
        } catch (\Throwable $e) {
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

    /** @param array{width?: float, height?: float, landscape?: bool} $paper */
    private static function renderViaCdp(
        string $binary,
        string $htmlFile,
        string $profileDir,
        int $waitMs,
        array $paper
    ): ?string {
        $port = self::freePort();
        $chrome = new Process([
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
            '--remote-debugging-port=' . $port,
            '--user-data-dir=' . $profileDir,
            self::fileUrl($htmlFile),
        ]);
        $chrome->setTimeout(15);
        $chrome->start();

        try {
            self::waitForChromePort($port, 4);

            $wsUrl = self::waitForDebuggerUrl($port, 4);
            if ($wsUrl === null) {
                return null;
            }

            $client = new ChromeDevtools($wsUrl);
            $client->call('Page.enable');

            // Fonts are embedded as data-URIs; a short settle is enough.
            usleep(max(250000, min(900000, $waitMs * 200)));

            try {
                $client->call('Runtime.evaluate', [
                    'expression' => 'document.fonts && document.fonts.ready ? document.fonts.ready.then(() => 1) : 1',
                    'awaitPromise' => true,
                    'returnByValue' => true,
                ]);
            } catch (\Throwable) {
                // ignore font wait failures
            }

            $printed = $client->call('Page.printToPDF', [
                'landscape' => (bool) ($paper['landscape'] ?? true),
                'paperWidth' => (float) ($paper['width'] ?? 8.27),
                'paperHeight' => (float) ($paper['height'] ?? 5.83),
                'printBackground' => true,
                'preferCSSPageSize' => true,
                'marginTop' => 0,
                'marginBottom' => 0,
                'marginLeft' => 0,
                'marginRight' => 0,
            ]);

            $client->close();

            $data = $printed['result']['data'] ?? null;
            if (!is_string($data) || $data === '') {
                return null;
            }

            $pdf = base64_decode($data, true);
            if ($pdf === false || strlen($pdf) < 1000) {
                return null;
            }

            return $pdf;
        } catch (\Throwable $e) {
            Log::warning('BrowserPdf CDP: ' . $e->getMessage());

            return null;
        } finally {
            if ($chrome->isRunning()) {
                $chrome->stop(1);
            }
        }
    }

    private static function renderViaCli(
        string $binary,
        string $htmlFile,
        string $pdfFile,
        string $profileDir,
        int $waitMs
    ): ?string {
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
            '--virtual-time-budget=' . max(400, $waitMs),
            '--run-all-compositor-stages-before-draw',
            '--no-pdf-header-footer',
            '--print-to-pdf=' . $pdfFile,
            self::fileUrl($htmlFile),
        ]);
        $process->setTimeout(15);
        $process->run();

        if (!is_file($pdfFile) || filesize($pdfFile) < 1000) {
            Log::warning('BrowserPdf: chrome produced no output', [
                'exit' => $process->getExitCode(),
                'stderr' => mb_substr($process->getErrorOutput(), 0, 2000),
            ]);

            return null;
        }

        return (string) file_get_contents($pdfFile);
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

        foreach (['google-chrome-stable', 'google-chrome', 'chromium-browser', 'chromium', 'chrome'] as $command) {
            $resolved = self::which($command);
            if ($resolved !== null) {
                return $resolved;
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
            '/usr/bin/google-chrome-stable',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/snap/bin/chromium',
            '/opt/google/chrome/google-chrome',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        ];
    }

    private static function which(string $command): ?string
    {
        $finder = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$finder, $command]);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        foreach (preg_split('/\R+/', trim($process->getOutput())) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '' && is_file($line)) {
                return $line;
            }
        }

        return null;
    }

    private static function freePort(): int
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            return random_int(20000, 60000);
        }

        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        if (!is_string($address) || !str_contains($address, ':')) {
            return random_int(20000, 60000);
        }

        return (int) substr($address, strrpos($address, ':') + 1);
    }

    private static function waitForChromePort(int $port, int $seconds): void
    {
        $deadline = microtime(true) + $seconds;
        while (microtime(true) < $deadline) {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.5);
            if ($socket !== false) {
                fclose($socket);

                return;
            }
            usleep(100000);
        }

        throw new \RuntimeException('Chrome remote debugging port did not open.');
    }

    private static function waitForDebuggerUrl(int $port, int $seconds): ?string
    {
        $deadline = microtime(true) + $seconds;
        while (microtime(true) < $deadline) {
            $list = self::httpGetJson('http://127.0.0.1:' . $port . '/json/list');
            if (is_array($list)) {
                foreach ($list as $target) {
                    if (
                        is_array($target)
                        && ($target['type'] ?? '') === 'page'
                        && !empty($target['webSocketDebuggerUrl'])
                    ) {
                        return (string) $target['webSocketDebuggerUrl'];
                    }
                }
                foreach ($list as $target) {
                    if (is_array($target) && !empty($target['webSocketDebuggerUrl'])) {
                        return (string) $target['webSocketDebuggerUrl'];
                    }
                }
            }
            usleep(120000);
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    private static function httpGetJson(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if (!is_string($body) || $body === '') {
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }

    private static function fileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        return 'file:///' . ltrim($normalized, '/');
    }
}
