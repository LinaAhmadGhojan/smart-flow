<?php

namespace App\Support;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CompanySettings
{
    private const ROW_ID = 1;

    /** @return list<string> */
    public static function legacyJsonPaths(): array
    {
        return [
            base_path('company-info.json'),
            public_path('company-info.json'),
        ];
    }

    public static function read(): array
    {
        $settings = self::readFromDatabase();

        if ($settings === []) {
            $settings = self::importFromLegacyJson();
        }

        $resolvedLogo = self::resolveBrandingWebPath($settings['logo'] ?? null, 'logo');
        $resolvedSignature = self::resolveBrandingWebPath($settings['signature'] ?? null, 'signature');

        if ($resolvedLogo !== null) {
            $settings['logo'] = $resolvedLogo;
        }
        if ($resolvedSignature !== null) {
            $settings['signature'] = $resolvedSignature;
        }

        $original = self::readFromDatabase();
        $needsPersist = $original === []
            || ($resolvedLogo !== ($original['logo'] ?? null))
            || ($resolvedSignature !== ($original['signature'] ?? null));

        if ($needsPersist && $settings !== [] && self::databaseReady()) {
            self::persistToDatabase($settings);
        }

        return $settings;
    }

    public static function write(array $settings): void
    {
        if (!self::databaseReady()) {
            throw new RuntimeException('company_settings table is not available.');
        }

        self::persistToDatabase($settings);
    }

    public static function brandingDir(): string
    {
        $dir = public_path('storage/branding');

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        return $dir;
    }

    public static function brandingResponse(array $settings): array
    {
        return [
            'logo' => isset($settings['logo']) ? StorageUrl::toPublicUrl($settings['logo']) : null,
            'signature' => isset($settings['signature']) ? StorageUrl::toPublicUrl($settings['signature']) : null,
            'signatureName' => $settings['signatureName'] ?? null,
        ];
    }

    public static function importFromLegacyJson(): array
    {
        $merged = [];

        foreach (self::legacyJsonPaths() as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $chunk = json_decode(File::get($path), true);
            if (is_array($chunk)) {
                $merged = array_replace_recursive($merged, $chunk);
            }
        }

        if ($merged !== [] && self::databaseReady()) {
            self::persistToDatabase($merged);
        }

        return $merged;
    }

    private static function readFromDatabase(): array
    {
        if (!self::databaseReady()) {
            return [];
        }

        $row = CompanySetting::query()->find(self::ROW_ID);

        return is_array($row?->settings) ? $row->settings : [];
    }

    private static function persistToDatabase(array $settings): void
    {
        CompanySetting::query()->updateOrCreate(
            ['id' => self::ROW_ID],
            ['settings' => $settings]
        );
    }

    private static function databaseReady(): bool
    {
        try {
            return Schema::hasTable('company_settings');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function resolveBrandingWebPath(?string $stored, string $prefix): ?string
    {
        if ($stored) {
            $absolute = StorageUrl::toFilesystemPath($stored);
            if ($absolute && File::exists($absolute)) {
                return $stored;
            }
        }

        $dir = self::brandingDir();
        $pattern = $dir . DIRECTORY_SEPARATOR . $prefix . '-*.*';
        $files = glob($pattern) ?: [];

        if ($files === []) {
            return $stored;
        }

        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return '/storage/branding/' . basename($files[0]);
    }
}
