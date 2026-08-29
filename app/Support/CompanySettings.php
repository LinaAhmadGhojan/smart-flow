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
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    private static function writeFile(string $path, string $json): void
    {
        $dir = dirname($path);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
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
    }
}
