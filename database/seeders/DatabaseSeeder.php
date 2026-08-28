<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin
        Admin::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'info@smartflow.ae')],
            [
                'password' => Hash::make(env('ADMIN_PASSWORD', '')),
            ]
        );

        $this->command->info('Default admin created successfully!');
        $this->command->info('Email: ' . env('ADMIN_EMAIL', 'info@smartflow.ae'));
        
        // Seed categories and products
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            OfferSeeder::class,
            ProjectSeeder::class,
            ReportSeeder::class,
            QuotationSeeder::class,
            DemoProjectSeeder::class,
        ]);
    }
}
