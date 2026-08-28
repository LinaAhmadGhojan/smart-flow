<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Luxury Villa Smart Home Installation',
                'title_ar' => 'تركيب منزل ذكي في فيلا فاخرة',
                'description' => 'Complete smart home automation system installed in a luxury villa in Dubai. Including automated lighting, climate control, security systems, and energy management. The project was completed in 3 months and has been running smoothly for over 2 years.',
                'description_ar' => 'نظام أتمتة منزلية ذكية كامل تم تركيبه في فيلا فاخرة في دبي. يشمل الإضاءة الآلية، التحكم في المناخ، أنظمة الأمن، وإدارة الطاقة. تم إنجاز المشروع في 3 أشهر ويعمل بسلاسة لأكثر من عامين.',
                'media_type' => 'image',
                'media_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
                'order' => 1,
                'is_featured' => true,
            ],
            [
                'title' => 'Commercial Building Gate Automation',
                'title_ar' => 'أتمتة بوابة مبنى تجاري',
                'description' => 'Automated sliding gate system for a commercial building in Abu Dhabi. NICE THOR 1500 KG motor with advanced security features, remote access control, and integration with building management system.',
                'description_ar' => 'نظام بوابة منزلقة أوتوماتيكية لمبنى تجاري في أبوظبي. موتور NICE THOR 1500 KG مع ميزات أمان متقدمة، تحكم عن بعد، وتكامل مع نظام إدارة المبنى.',
                'media_type' => 'image',
                'media_url' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800',
                'order' => 2,
                'is_featured' => true,
            ],
            [
                'title' => 'Residential Complex Security System',
                'title_ar' => 'نظام أمني لمجمع سكني',
                'description' => 'Comprehensive security system for a 50-unit residential complex. Including CCTV cameras, access control, intercom system, and fire alarm integration. 24/7 monitoring and mobile app access for residents.',
                'description_ar' => 'نظام أمني شامل لمجمع سكني مكون من 50 وحدة. يشمل كاميرات المراقبة، التحكم في الوصول، نظام الاتصال الداخلي، والتكامل مع إنذار الحريق. مراقبة على مدار 24/7 والوصول عبر تطبيق الهاتف المحمول للسكان.',
                'media_type' => 'image',
                'media_url' => 'https://images.unsplash.com/photo-1558002038-bb4237b54546?w=800',
                'order' => 3,
                'is_featured' => false,
            ],
            [
                'title' => 'Office Building Energy Management',
                'title_ar' => 'إدارة الطاقة لمبنى مكاتب',
                'description' => 'Smart energy management system for a 10-floor office building. Reduced energy consumption by 35% through automated climate control, smart lighting, and real-time monitoring. ROI achieved in 18 months.',
                'description_ar' => 'نظام إدارة طاقة ذكي لمبنى مكاتب من 10 طوابق. تم تقليل استهلاك الطاقة بنسبة 35% من خلال التحكم الآلي في المناخ، الإضاءة الذكية، والمراقبة في الوقت الفعلي. تم تحقيق عائد الاستثمار في 18 شهرًا.',
                'media_type' => 'image',
                'media_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
                'order' => 4,
                'is_featured' => false,
            ],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
        
        $this->command->info('Projects seeded successfully!');
    }
}
