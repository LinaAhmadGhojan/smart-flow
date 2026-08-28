<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Smart Home Systems',
                'name_ar' => 'أنظمة المنزل الذكي',
                'description' => 'Complete smart home automation solutions',
            ],
            [
                'name' => 'Gate Motors',
                'name_ar' => 'محركات البوابات',
                'description' => 'Automatic gate motors and systems',
            ],
            [
                'name' => 'Security Systems',
                'name_ar' => 'أنظمة الأمان',
                'description' => 'Advanced security and surveillance systems',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
        
        $this->command->info('Categories seeded successfully!');
    }
}
