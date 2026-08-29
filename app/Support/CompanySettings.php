<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use RuntimeException;

class CompanySettings
{
    /** @return list<string> */
    public static function paths(): array
    {
        return [
            base_path('company-info.json'),
            public_path('company-info.json'),
        ];
    }

    public static function read(): array
    {
        $merged = [];

        foreach (self::paths() as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $chunk = json_decode(File::get($path), true);
            if (is_array($chunk)) {
                $merged = array_replace_recursive($merged, $chunk);
            }
        }

        $resolvedLogo = self::resolveBrandingWebPath($merged['logo'] ?? null, 'logo');
        $resolvedSignature = self::resolveBrandingWebPath($merged['signature'] ?? null, 'signature');

        $needsPersist = ($resolvedLogo !== ($merged['logo'] ?? null))
            || ($resolvedSignature !== ($merged['signature'] ?? null));

        if ($resolvedLogo !== null) {
            $merged['logo'] = $resolvedLogo;
        }
        if ($resolvedSignature !== null) {
            $merged['signature'] = $resolvedSignature;
        }

        if ($needsPersist && $merged !== []) {
            try {
                self::write($merged);
            } catch (\Throwable) {
                // Still return resolved paths for display even if persist fails.
            }
        }

        return $merged;
    }

    public static function write(array $settings): void
    {
        $json = json_encode(
            $settings,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException('Failed to encode company settings JSON.');
        }

        $written = false;
        $errors = [];

        foreach (self::paths() as $path) {
            try {
                self::writeFile($path, $json);
                $written = true;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$written) {
            throw new RuntimeException(
                'Cannot write company-info.json: ' . implode(' | ', $errors)
            );
        }
    }

    public static function brandingDir(): string
    {
        $dir = public_path('storage/branding');

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        return $dir;
    }

    public static function brandingResponse(array $settings): array
    {
        return [
            'logo' => isset($settings['logo']) ? StorageUrl::toPublicUrl($settings['logo']) : null,
            'signature' => isset($settings['signature']) ? StorageUrl::toPublicUrl($settings['signature']) : null,
            'signatureName' => $settings['signatureName'] ?? null,
        ];
    }

    private static function resolveBrandingWebPath(?string $stored, string $prefix): ?string
    {
        if ($stored) {
            $absolute = StorageUrl::toFilesystemPath($stored);
            if ($absolute && File::exists($absolute)) {
                return $stored;
            }
        }

        $dir = self::brandingDir();
        $pattern = $dir . DIRECTORY_SEPARATOR . $prefix . '-*.*';
        $files = glob($pattern) ?: [];

        if ($files === []) {
            return $stored;
        }

        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return '/storage/branding/' . basename($files[0]);
    }

    private static function writeFile(string $path, string $json): void
    {
        $dir = dirname($path);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        if (File::exists($path) && !is_writable($path)) {
            @chmod($path, 0664);
        }

        if (!File::exists($path) && !is_writable($dir)) {
            @chmod($dir, 0775);
        }

        if (File::exists($path) && !is_writable($path)) {
            throw new RuntimeException("Not writable: {$path}");
        }

        if (!File::exists($path) && !is_writable($dir)) {
            throw new RuntimeException("Directory not writable: {$dir}");
        }

        if (File::put($path, $json) === false) {
            throw new RuntimeException("Failed to write: {$path}");
        }

        @chmod($path, 0664);
    }
}
