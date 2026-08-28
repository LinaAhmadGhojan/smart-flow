<?php

$src = imagecreatefrompng(__DIR__ . '/wave-crop-new.png');
$w = imagesx($src);
$h = imagesy($src);

$out = imagecreatetruecolor($w, $h);
$white = imagecolorallocate($out, 255, 255, 255);
imagefilledrectangle($out, 0, 0, $w, $h, $white);

for ($x = 0; $x < $w; $x++) {
    $bottomBlue = null;
    for ($y = $h - 1; $y >= 0; $y--) {
        $c = imagecolorat($src, $x, $y);
        $r = ($c >> 16) & 0xFF;
        $g = ($c >> 8) & 0xFF;
        $b = $c & 0xFF;
        $isBlue = $b > 80 && $b - $r > 30 && $r < 120;
        if ($isBlue) {
            $bottomBlue = $y;
            break;
        }
    }
    if ($bottomBlue === null) {
        continue;
    }
    for ($y = $bottomBlue; $y >= 0; $y--) {
        $c = imagecolorat($src, $x, $y);
        $r = ($c >> 16) & 0xFF;
        $g = ($c >> 8) & 0xFF;
        $b = $c & 0xFF;
        $isBlue = $b > 80 && $b - $r > 30 && $r < 160;
        if (!$isBlue) {
            break;
        }
        imagesetpixel($out, $x, $y, imagecolorallocate($out, $r, $g, $b));
    }
    if ($bottomBlue !== null) {
        $fill = imagecolorallocate($out, 26, 67, 127);
        for ($y = $bottomBlue; $y < $h; $y++) {
            imagesetpixel($out, $x, $y, $fill);
        }
    }
}

imagepng($out, __DIR__ . '/wave-clean.png');
echo "done {$w}x{$h}\n";
