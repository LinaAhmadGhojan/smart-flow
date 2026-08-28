<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeDatabaseExceptCore extends Command
{
    protected $signature = 'db:purge-except-core {--force : Skip confirmation}';

    protected $description = 'Delete all data except products, customer reviews, and admins';

    /** @var list<string> */
    private array $preserve = [
        'admins',
        'products',
        'reviews',
        'migrations',
    ];

    /** @var list<string> Order: child tables first */
    private array $purge = [
        'quotation_items',
        'invoices',
        'project_payments',
        'project_expenses',
        'project_delivery_notes',
        'project_contacts',
        'project_files',
        'project_profit_shares',
        'reports',
        'study_requests',
        'gate_machine_study_requests',
        'quotations',
        'projects',
        'appointment_slots',
        'customers',
        'engineers',
        'offers',
        'personal_access_tokens',
        'categories',
        'product_groups',
    ];

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Delete ALL data except products, reviews, and admins?')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();

        DB::table('products')->update([
            'category_id' => null,
            'group_id' => null,
        ]);

        $counts = [];
        foreach ($this->purge as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $counts[$table] = DB::table($table)->count();
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Purged tables:');
        foreach ($counts as $table => $count) {
            $this->line("  - {$table}: {$count} rows deleted");
        }

        $this->newLine();
        $this->info('Preserved:');
        foreach ($this->preserve as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $this->line('  - ' . $table . ': ' . DB::table($table)->count() . ' rows');
        }

        return self::SUCCESS;
    }
}
