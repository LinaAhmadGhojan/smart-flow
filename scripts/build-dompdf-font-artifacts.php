<?php

/**
 * Build committed Dompdf font artifacts for Hostinger (path-specific hash).
 * Run: php scripts/build-dompdf-font-artifacts.php
 */
require __DIR__ . '/../vendor/autoload.php';

use FontLib\Font;

$root = realpath(__DIR__ . '/..');
$fontDir = $root . '/storage/fonts';
$hostRoot = '/home/u696702336/domains/smartflowuae.com/public_html';

if (!is_dir($fontDir)) {
    mkdir($fontDir, 0775, true);
}

/** @var array<string, array{source:string, url:string, variants:array<string,string>}> */
$fonts = [
    'arabicreport' => [
        'source' => $root . '/resources/fonts/NotoNaskhArabic-Regular.ttf',
        'url' => 'file://' . $hostRoot . '/resources/fonts/NotoNaskhArabic-Regular.ttf',
        'variants' => ['normal' => 'normal'],
    ],
    'arreg' => [
        'source' => $root . '/resources/fonts/Cairo-Regular.ttf',
        'url' => 'file://' . $hostRoot . '/resources/fonts/Cairo-Regular.ttf',
        'variants' => ['normal' => 'normal'],
    ],
    'arbold' => [
        'source' => $root . '/resources/fonts/Cairo-Bold.ttf',
        'url' => 'file://' . $hostRoot . '/resources/fonts/Cairo-Bold.ttf',
        'variants' => ['normal' => 'normal'],
    ],
];

$installed = [];

foreach ($fonts as $family => $cfg) {
    if (!is_file($cfg['source'])) {
        fwrite(STDERR, "Missing source font: {$cfg['source']}\n");
        continue;
    }

    foreach ($cfg['variants'] as $styleString => $cssWeightStyle) {
        $hash = md5($cfg['url']);
        $prefix = $family . '_' . $styleString;
        $base = $prefix . '_' . $hash;
        $ufm = $fontDir . '/' . $base . '.ufm';
        $ttf = $fontDir . '/' . $base . '.ttf';

        if (!is_file($ufm)) {
            $font = Font::load($cfg['source']);
            if (!$font) {
                throw new RuntimeException("Failed to load font: {$cfg['source']}");
            }
            $font->parse();
            $font->saveAdobeFontMetrics($ufm);
            $font->close();
            echo "Generated {$base}.ufm\n";
        }

        if (!is_file($ttf)) {
            copy($cfg['source'], $ttf);
            echo "Copied {$base}.ttf\n";
        }

        $installed[$family][$styleString] = $base;
    }
}

$jsonPath = $fontDir . '/installed-fonts.json';
file_put_contents($jsonPath, json_encode($installed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "Wrote installed-fonts.json\n";
