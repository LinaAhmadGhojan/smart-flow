<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class FinancePdfBranding
{
    /** @return array<string, mixed> */
    public static function companyViewData(array $company, ?string $logoPath, ?string $signaturePath): array
    {
        $contact = is_array($company['contact'] ?? null) ? $company['contact'] : [];
        $addressAr = is_array($contact['address'] ?? null)
            ? (($contact['address']['ar'] ?? null) ?: ($contact['address']['en'] ?? 'الإمارات العربية المتحدة'))
            : 'الإمارات العربية المتحدة';

        $companyNameAr = preg_replace('/\x{0640}/u', '', (string) ($company['companyNameAr'] ?? $company['companyName'] ?? 'التدفق الذكي'));
        $tagline = 'للأنظمة الذكية والحلول التقنية';
        if (str_contains($companyNameAr, $tagline)) {
            $companyNameAr = trim(str_replace($tagline, '', $companyNameAr));
        }

        $icons = self::icons();

        return array_merge($icons, [
            'companyNameAr' => $companyNameAr,
            'companyNameEn' => (string) ($company['companyName'] ?? 'SMART FLOW'),
            'phone' => (string) ($contact['phone'] ?? '+971'),
            'email' => (string) ($contact['email'] ?? 'info@smartflow.ae'),
            'addressAr' => (string) $addressAr,
            'trn' => trim((string) ($company['trn'] ?? ($company['taxNumber'] ?? ''))),
            'companyLegalName' => (string) ($company['legalName']
                ?? 'AL TDFUQ AL DHAKI ELECTRICITY TRANSMISSION & CONTROL EQUIPMENT INSTALLATION WORKS L.L.C'),
            'companyCountry' => (string) ($contact['address']['en']
                ?? ($company['seo']['location']['country'] ?? 'United Arab Emirates')),
            'signatureName' => $company['signatureName'] ?? null,
            'logoDataUri' => self::toDataUri($logoPath, 350),
            'signatureDataUri' => self::toDataUri($signaturePath, 180),
            'arabicFontUrl' => DompdfFontCache::arabicFontUrl(),
            'fontEmbedCss' => self::fontEmbedCss(),
        ]);
    }

    public static function fontEmbedCss(): string
    {
        $faces = [
            ['Cairo', 400, 'Cairo-Regular.ttf'],
            ['Cairo', 700, 'Cairo-Bold.ttf'],
            ['CairoFallback', 400, 'NotoSansArabic-Regular.ttf'],
            ['CairoFallback', 700, 'NotoSansArabic-Bold.ttf'],
        ];

        $css = '';
        foreach ($faces as [$family, $weight, $file]) {
            $path = resource_path('fonts/' . $file);
            if (!is_file($path)) {
                continue;
            }

            $css .= sprintf(
                "@font-face{font-family:'%s';font-style:normal;font-weight:%d;font-display:block;"
                . "src:url(data:font/ttf;base64,%s) format('truetype');}\n",
                $family,
                $weight,
                base64_encode((string) file_get_contents($path))
            );
        }

        return $css;
    }

    public static function absoluteAssetPath(?string $webPath): ?string
    {
        if (!$webPath) {
            return null;
        }

        $webPath = preg_replace('#^/public/#', '/', $webPath) ?? $webPath;
        $relative = ltrim($webPath, '/');
        $candidates = [
            public_path($relative),
            storage_path('app/public/' . ltrim(str_replace(['/storage/', 'storage/'], '', $webPath), '/')),
        ];

        foreach (array_unique($candidates) as $absolute) {
            if ($absolute && File::exists($absolute) && is_file($absolute)) {
                return $absolute;
            }
        }

        return null;
    }

    /** @return array<string, string|null> */
    public static function icons(): array
    {
        $blue = '#1a437f';
        $light = '#5b8fd4';
        $iconsDir = resource_path('images/receipt');

        $pngUri = static function (string $path) {
            if (!is_file($path)) {
                return null;
            }

            return 'data:image/png;base64,' . base64_encode((string) file_get_contents($path));
        };

        $svg = static fn (string $body, int $w = 32, int $h = 32) => 'data:image/svg+xml;base64,' . base64_encode(
            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" fill="none">' . $body . '</svg>'
        );

        return [
            'iconPhone' => $svg(
                '<path d="M11 4 C9.4 4 8.2 5.3 8.4 6.9 C9 11.2 11.1 15.1 14.3 18.3 C17.5 21.5 21.4 23.6 25.7 24.2 C27.3 24.4 28.6 23.2 28.6 21.6 V19.1 C28.6 18.3 28.1 17.7 27.3 17.4 L24.1 16.4 C23.5 16.2 22.8 16.4 22.4 16.9 L20.9 18.7 C18.3 17.5 16.1 15.3 14.9 12.7 L16.7 11.2 C17.2 10.8 17.4 10.1 17.2 9.5 L16.2 6.3 C16 5.5 15.4 5 14.6 5 Z" fill="' . $blue . '"/>'
            ),
            'iconEmail' => $svg(
                '<rect x="4" y="8" width="24" height="18" rx="2.2" stroke="' . $blue . '" stroke-width="2" fill="none"/>'
                . '<path d="M5.5 10.5 L16 18 L26.5 10.5" stroke="' . $blue . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
            ),
            'iconLocation' => $svg(
                '<path d="M16 3 C11.6 3 8 6.7 8 11.2 C8 17.4 16 26 16 26 C16 26 24 17.4 24 11.2 C24 6.7 20.4 3 16 3 Z" fill="' . $blue . '"/>'
                . '<circle cx="16" cy="11" r="3.2" fill="#fff"/>'
            ),
            'waveSvg' => $pngUri($iconsDir . DIRECTORY_SEPARATOR . 'wave.png') ?: 'data:image/svg+xml;base64,' . base64_encode(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="89" viewBox="0 0 1024 89" preserveAspectRatio="none">'
                . '<path d="M0,18 C70,38 160,50 260,58 C360,66 460,70 512,71 C620,70 780,52 1024,11 L1024,89 L0,89 Z" fill="' . $light . '"/>'
                . '<path d="M0,31 C90,48 200,60 340,67 C480,73 620,68 760,55 C860,45 950,28 1024,11 L1024,89 L0,89 Z" fill="' . $blue . '"/>'
                . '</svg>'
            ),
        ];
    }

    public static function toDataUri(?string $absolutePath, int $maxSide = 0): ?string
    {
        if (!$absolutePath || !File::exists($absolutePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (function_exists('imagecreatefromstring')) {
            $raw = @File::get($absolutePath);
            $src = $raw ? @imagecreatefromstring($raw) : false;
            if ($src !== false) {
                $w = imagesx($src);
                $h = imagesy($src);
                if ($maxSide > 0 && ($w > $maxSide || $h > $maxSide)) {
                    $scale = min($maxSide / $w, $maxSide / $h);
                    $nw = max(1, (int) round($w * $scale));
                    $nh = max(1, (int) round($h * $scale));
                    $dst = imagecreatetruecolor($nw, $nh);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    imagedestroy($src);
                    $src = $dst;
                }
                ob_start();
                imagepng($src);
                $png = ob_get_clean();
                imagedestroy($src);

                return $png ? 'data:image/png;base64,' . base64_encode($png) : null;
            }
        }

        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) File::get($absolutePath));
    }
}
