<?php

$src = imagecreatefromstring((string) file_get_contents(__DIR__ . '/mockup.png'));
$dir = __DIR__;

$crop = static function ($img, $x, $y, $w, $h, $name) use ($dir) {
    $out = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($out, 255, 255, 255);
    imagefilledrectangle($out, 0, 0, $w, $h, $white);
    imagecopy($out, $img, 0, 0, $x, $y, $w, $h);
    imagepng($out, $dir . '/' . $name);
};

// Zoom the three payment columns so we can pick icon boxes
$crop($src, 70, 330, 300, 70, 'col-cash.png');
$crop($src, 360, 320, 300, 80, 'col-bank.png');
$crop($src, 650, 330, 300, 70, 'col-cheque.png');
$crop($src, 150, 530, 720, 50, 'contact-row.png');
