<?php

$file = __DIR__ . '/receipt-browser.pdf';
$raw = (string) file_get_contents($file);

preg_match_all('#/MediaBox\s*\[([^\]]+)\]#', $raw, $boxes);
echo "MediaBox: " . implode(' | ', array_unique($boxes[1] ?? [])) . "\n";
echo "Pages: " . preg_match_all('#/Type\s*/Page[^s]#', $raw) . "\n";

preg_match_all('#/BaseFont\s*/([A-Za-z0-9+\-_,.]+)#', $raw, $fonts);
echo "Fonts: " . implode(', ', array_unique($fonts[1] ?? [])) . "\n";

echo "Images (XObject): " . preg_match_all('#/Subtype\s*/Image#', $raw) . "\n";
echo "Size: " . strlen($raw) . " bytes\n";
