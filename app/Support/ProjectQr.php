<?php

namespace App\Support;

use App\Models\Project;

class ProjectQr
{
    public static function payload(Project $project): string
    {
        $project->loadMissing(['customer', 'contacts']);

        $lines = [
            'SmartFlow — Project',
            'المشروع: ' . ($project->title_ar ?: $project->title),
        ];

        if ($project->title_ar && $project->title) {
            $lines[] = 'Project: ' . $project->title;
        }

        if ($project->customer?->name) {
            $lines[] = 'العميل: ' . $project->customer->name;
        }

        if ($project->location) {
            $lines[] = 'الموقع: ' . $project->location;
        }

        foreach ($project->contacts as $contact) {
            $line = 'جهة اتصال: ' . $contact->name;
            if ($contact->phone) {
                $line .= ' — ' . $contact->phone;
            }
            $lines[] = $line;
        }

        if ($project->maps_url) {
            $lines[] = 'Maps: ' . $project->maps_url;
        }

        return implode("\n", $lines);
    }

    public static function generateAndSave(Project $project): ?string
    {
        $payload = self::payload($project);
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&format=png&data=' . rawurlencode($payload);
        $png = @file_get_contents($apiUrl);
        if ($png === false || $png === '') {
            return null;
        }

        $dir = public_path('storage/projects/qr');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($project->qr_path && str_starts_with($project->qr_path, '/storage/')) {
            $old = public_path(str_replace('/storage/', 'storage/', $project->qr_path));
            if (is_file($old)) {
                @unlink($old);
            }
        }

        $filename = 'qr-' . $project->id . '-' . time() . '.png';
        file_put_contents($dir . '/' . $filename, $png);

        return '/storage/projects/qr/' . $filename;
    }
}
