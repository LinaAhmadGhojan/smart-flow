<?php

namespace App\Support;

use FontLib\Font;
use Illuminate\Support\Facades\File;

class DompdfFontCache
{
    /** Hostinger absolute project root (used for stable Dompdf font URL hashes). */
    private const HOST_ROOT = '/home/u696702336/domains/smartflowuae.com/public_html';

    public static function ensureReady(): void
    {
        $dir = storage_path('fonts');

        if (!is_dir($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        @chmod($dir, 0775);

        self::ensureInstalled('arabicreport', resource_path('fonts/NotoNaskhArabic-Regular.ttf'), 'normal');
        self::ensureInstalled('arreg', resource_path('fonts/Cairo-Regular.ttf'), 'normal');
        self::ensureInstalled('arbold', resource_path('fonts/Cairo-Bold.ttf'), 'normal');
    }

    public static function arabicFontUrl(): string
    {
        self::ensureReady();

        return self::fileUrl(resource_path('fonts/NotoNaskhArabic-Regular.ttf'));
    }

    private static function fileUrl(string $absolutePath): string
    {
        $normalized = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($normalized, self::HOST_ROOT)) {
            return 'file://' . $normalized;
        }

        $real = str_replace('\\', '/', realpath($absolutePath) ?: $absolutePath);

        if (preg_match('#^[A-Za-z]:/#', $real)) {
            return 'file:///' . $real;
        }

        return 'file://' . $real;
    }

    private static function ensureInstalled(string $family, string $sourceTtf, string $styleString): void
    {
        if (!is_file($sourceTtf)) {
            return;
        }

        $fontDir = storage_path('fonts');
        $url = self::fileUrl($sourceTtf);
        $hash = md5($url);
        $base = strtolower($family) . '_' . $styleString . '_' . $hash;
        $ufm = $fontDir . DIRECTORY_SEPARATOR . $base . '.ufm';
        $destTtf = $fontDir . DIRECTORY_SEPARATOR . $base . '.ttf';

        if (is_file($ufm) && is_file($destTtf)) {
            self::mergeInstalledFonts(strtolower($family), $styleString, $base);

            return;
        }

        if (!is_writable($fontDir)) {
            return;
        }

        if (!is_file($ufm)) {
            $font = Font::load($sourceTtf);
            if (!$font) {
                return;
            }
            $font->parse();
            $font->saveAdobeFontMetrics($ufm);
            $font->close();
        }

        if (!is_file($destTtf)) {
            copy($sourceTtf, $destTtf);
        }

        self::mergeInstalledFonts(strtolower($family), $styleString, $base);
    }

    private static function mergeInstalledFonts(string $family, string $styleString, string $base): void
    {
        $jsonPath = storage_path('fonts/installed-fonts.json');
        $data = [];

        if (is_file($jsonPath)) {
            $decoded = json_decode((string) file_get_contents($jsonPath), true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $data[$family][$styleString] = $base;

        if (is_writable(dirname($jsonPath))) {
            file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        }
    }
}
