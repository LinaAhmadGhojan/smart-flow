<?php

namespace App\Support;

class StorageUrl
{
    /**
     * Web URL for files under public/storage.
     * - Hostinger (docroot = public_html): /public/storage/...
     * - php artisan serve (docroot = public): /storage/...
     */
    public static function toPublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = preg_replace('#^/uploads/#', '/storage/', $path) ?? $path;
        $path = preg_replace('#^/public/storage/#', '/storage/', $path) ?? $path;

        if (!str_starts_with($path, '/storage/')) {
            $path = str_starts_with($path, '/')
                ? '/storage' . $path
                : '/storage/' . $path;
        }

        return self::needsPublicPrefix() ? '/public' . $path : $path;
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

    /** True when web root is project root (Hostinger), false when web root is public/ (artisan serve). */
    public static function needsPublicPrefix(): bool
    {
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if ($docRoot === '') {
            return true;
        }

        $docRoot = str_replace('\\', '/', realpath($docRoot) ?: $docRoot);
        $publicPath = str_replace('\\', '/', realpath(public_path()) ?: public_path());

        return rtrim($docRoot, '/') !== rtrim($publicPath, '/');
    }
}
