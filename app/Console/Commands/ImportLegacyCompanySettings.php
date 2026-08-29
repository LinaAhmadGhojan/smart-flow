<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Support\CompanySettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ImportLegacyCompanySettings extends Command
{
    protected $signature = 'company-settings:import-legacy';

    protected $description = 'Import company settings from legacy company-info.json files into the database';

    public function handle(): int
    {
        if (!Schema::hasTable('company_settings')) {
            $this->warn('company_settings table missing. Run: php artisan migrate --force');

            return self::FAILURE;
        }

        if (CompanySetting::query()->find(1)) {
            $this->info('Company settings already exist in database — skipped.');

            return self::SUCCESS;
        }

        $settings = CompanySettings::importFromLegacyJson();

        if ($settings === []) {
            $this->warn('No legacy company-info.json data found.');

            return self::SUCCESS;
        }

        $this->info('Imported company settings into database.');

        if (!empty($settings['logo'])) {
            $this->line('Logo: ' . $settings['logo']);
        }
        if (!empty($settings['signature'])) {
            $this->line('Signature: ' . $settings['signature']);
        }

        return self::SUCCESS;
    }
}
