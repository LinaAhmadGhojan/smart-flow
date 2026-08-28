<?php

$sw = 3176;
$sh = 208;
$src = imagecreatetruecolor($sw, $sh);
$white = imagecolorallocate($src, 255, 255, 255);
imagefilledrectangle($src, 0, 0, $sw, $sh, $white);

$navy = imagecolorallocate($src, 26, 67, 127);
$light = imagecolorallocate($src, 91, 143, 212);

$n = 400;
$navyTop = [];
$lightTop = [];
for ($i = 0; $i <= $n; $i++) {
    $t = $i / $n;
    $x = (int) round($t * ($sw - 1));
    $dip = sin(M_PI * $t);
    $navyY = (int) round(44 + 124 * $dip * $dip);
    $peek = 40 * exp(-10 * $t * $t);
    $lightY = (int) round($navyY - $peek);
    $navyTop[] = [$x, max(0, $navyY)];
    $lightTop[] = [$x, max(0, $lightY)];
}

$poly = static function (array $top) use ($sw, $sh): array {
    $pts = [];
    foreach ($top as [$x, $y]) {
        $pts[] = $x;
        $pts[] = $y;
    }
    $pts[] = $sw - 1;
    $pts[] = $sh - 1;
    $pts[] = 0;
    $pts[] = $sh - 1;
    return $pts;
};

imagefilledpolygon($src, $poly($lightTop), $light);
imagefilledpolygon($src, $poly($navyTop), $navy);

$w = 1588;
$h = 104;
$outImg = imagecreatetruecolor($w, $h);
imagecopyresampled($outImg, $src, 0, 0, 0, 0, $w, $h, $sw, $sh);

$out = __DIR__ . '/../../resources/images/receipt/wave.png';
imagepng($outImg, $out, 6);
echo 'wrote bytes=' . filesize($out) . "\n";
