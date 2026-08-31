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
                '<circle cx="16" cy="16" r="16" fill="' . $blue . '"/>'
                . '<path d="M13.2 11.2c-.7 0-1.2.6-1.1 1.3.4 2.5 1.8 4.8 3.7 6.7s4.2 3.3 6.7 3.7c.7.1 1.3-.4 1.3-1.1v-1.8c0-.5-.3-.9-.8-1l-2-.6c-.4-.1-.9.1-1.1.5l-.7 1c-1.5-.8-2.7-2-3.5-3.5l1-.7c.3-.4.4-.9 0-1.2l-.6-2c-.1-.5-.5-.8-1-.8h-1.8z" fill="#fff"/>'
            ),
            'iconEmail' => $svg(
                '<circle cx="16" cy="16" r="16" fill="' . $blue . '"/>'
                . '<rect x="9" y="11" width="14" height="10" rx="1.5" stroke="#fff" stroke-width="1.4" fill="none"/>'
                . '<path d="M9.8 12.2 L16 16.5 L22.2 12.2" stroke="#fff" stroke-width="1.4" stroke-linecap="round" fill="none"/>'
            ),
            'iconLocation' => $svg(
                '<circle cx="16" cy="16" r="16" fill="' . $blue . '"/>'
                . '<path d="M16 8.5c-2.8 0-5 2.3-5 5.1c0 3.8 5 9.4 5 9.4s5-5.6 5-9.4c0-2.8-2.2-5.1-5-5.1z" fill="#fff"/>'
                . '<circle cx="16" cy="13.6" r="1.8" fill="' . $blue . '"/>'
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
