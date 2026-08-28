<?php

require __DIR__ . '/../../vendor/autoload.php';

$dir = __DIR__ . '/../../resources/fonts';
foreach (glob($dir . '/*.ttf') as $path) {
    $font = FontLib\Font::load($path);
    $font->parse();
    $map = $font->getUnicodeCharMap() ?: [];

    $arabicBase = 0;
    $arabicForms = 0;
    foreach (array_keys($map) as $cp) {
        if ($cp >= 0x0600 && $cp <= 0x06FF) {
            $arabicBase++;
        } elseif ($cp >= 0xFE70 && $cp <= 0xFEFF) {
            $arabicForms++;
        }
    }

    printf(
        "%-32s glyphs=%-6d arabic_base=%-4d presentation_forms=%d\n",
        basename($path),
        count($map),
        $arabicBase,
        $arabicForms
    );
    $font->close();
}
