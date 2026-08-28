<?php

namespace App\Support;

class StorageUrl
{
    /** Web URL for files under public/storage (Hostinger docroot = public_html). */
    public static function toPublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = preg_replace('#^/uploads/#', '/public/storage/', $path) ?? $path;

        if (str_starts_with($path, '/storage/')) {
            return '/public' . $path;
        }

        if (!str_starts_with($path, '/public/storage/') && !str_starts_with($path, 'http')) {
            $path = str_starts_with($path, '/') ? '/public/storage' . $path : '/public/storage/' . $path;
        }

        return $path;
    }

    /** Absolute filesystem path for a stored web path (/storage/ or /public/storage/). */
    public static function toFilesystemPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $relative = preg_replace('#^/(?:public/)?storage/#', 'storage/', $path) ?? $path;
        $relative = preg_replace('#^/uploads/#', 'storage/', $relative) ?? $relative;

        return public_path(ltrim($relative, '/'));
    }
}
