<?php

$src = imagecreatefromstring((string) file_get_contents(__DIR__ . '/mockup-wave.png'));
$w = imagesx($src);
$h = imagesy($src);

$isNavy = static function ($img, $x, $y) {
    $c = imagecolorat($img, $x, $y);
    $r = ($c >> 16) & 0xFF;
    $g = ($c >> 8) & 0xFF;
    $b = $c & 0xFF;
    // Solid footer fill: dark navy, lots of pixels in a row
    return $r < 50 && $g < 90 && $b > 90 && $b - $r > 50;
};

$startY = 560;
$top = [];
for ($x = 0; $x < $w; $x++) {
    $found = null;
    for ($y = $startY; $y < $h; $y++) {
        if ($isNavy($src, $x, $y)) {
            $found = $y;
            break;
        }
    }
    $top[$x] = $found;
}

$ys = array_values(array_filter($top, fn ($v) => $v !== null));
$minY = min($ys);
$maxY = $h - 1;
echo "wave y={$minY}..{$maxY} height=" . ($maxY - $minY + 1) . "\n";

$samples = [];
for ($x = 0; $x < $w; $x += 16) {
    $y = $top[$x];
    if ($y !== null) {
        $samples[] = $x . ':' . ($y - $minY);
    }
}
echo implode(' ', $samples) . "\n";

$ch = $maxY - $minY + 1;
$out = imagecreatetruecolor($w, $ch);
$white = imagecolorallocate($out, 255, 255, 255);
imagefilledrectangle($out, 0, 0, $w, $ch, $white);
imagecopy($out, $src, 0, 0, 0, $minY, $w, $ch);
imagepng($out, __DIR__ . '/wave-crop-new.png');
echo "wrote {$w}x{$ch}\n";
