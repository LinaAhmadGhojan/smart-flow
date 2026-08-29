<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Support\CompanySettings;
use Illuminate\Console\Command;

class ImportLegacyCompanySettings extends Command
{
    protected $signature = 'company-settings:import-legacy';

    protected $description = 'Import company settings from legacy company-info.json files into the database';

    public function handle(): int
    {
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
