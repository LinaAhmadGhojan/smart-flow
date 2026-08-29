<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class DompdfFontCache
{
    public static function ensureReady(): void
    {
        $dir = storage_path('fonts');

        if (!is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }
}
