<?php
$path = $argv[1] ?? 'C:/Users/DELL/Downloads/RCP-1-001 (6).pdf';
$p = file_get_contents($path);
echo "file=$path\n";
echo 'bytes=' . strlen($p) . "\n";
echo 'pages=' . preg_match_all('#/Type\s*/Page[^s]#', $p) . "\n";
if (preg_match_all('/MediaBox\s*\[\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\]/', $p, $m, PREG_SET_ORDER)) {
    foreach ($m as $i => $box) {
        $w = ($box[3] - $box[1]) * 25.4 / 72;
        $h = ($box[4] - $box[2]) * 25.4 / 72;
        printf("page%d=%.1f x %.1f mm\n", $i + 1, $w, $h);
    }
}
echo 'has /MediaBox A4? ' . (str_contains($p, '841') || preg_match('/595\.?\d*\s+841/', $p) ? 'maybe' : 'no') . "\n";
