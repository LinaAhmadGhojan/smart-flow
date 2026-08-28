<?php

$src = imagecreatefromstring((string) file_get_contents(__DIR__ . '/mockup.png'));

// Bounds measured from the mockup: the wave band under the contact line.
$x = 55;
$y = 597;
$w = 926;
$h = 45;

$out = imagecreatetruecolor($w, $h);
imagecopy($out, $src, 0, 0, $x, $y, $w, $h);
imagepng($out, __DIR__ . '/wave-crop.png');
printf("written wave-crop.png (%dx%d) from x=%d y=%d\n", $w, $h, $x, $y);
