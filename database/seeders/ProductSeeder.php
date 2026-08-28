<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'NICE THOR 1500 KG',
                'name_ar' => 'موتور بوابة منزلقة',
                'description' => 'High-quality sliding gate motor with 1500 kg load capacity. Perfect for heavy-duty residential and commercial gates in Dubai, UAE.',
                'description_ar' => 'موتور بوابة منزلقة عالي الجودة بقدرة تحميل 1500 كجم. مثالي للبوابات السكنية والتجارية الثقيلة في دبي، الإمارات.',
                'brand' => 'NICE',
                'price' => '2000.00',
                'price_number' => 2000.00,
                'image' => '/uploads/products/nice-thor.jpg',
                'in_stock' => true,
                'category_id' => 2,
                'features' => [
                    'Sliding Gate Motor in Dubai, UAE',
                    '1500 kg load capacity',
                    'Electromechanical limit switch',
                    'Integrated control unit'
                ],
                'whatsapp_message' => 'مرحباً، أنا مهتم بمنتج NICE THOR 1500 KG موتور بوابة منزلقة',
            ],
            [
                'name' => 'Automatic Sliding Gate System',
                'name_ar' => 'نظام البوابة المنزلقة الأوتوماتيكية',
                'description' => 'Complete automation solution for sliding gates with remote control access and safety sensors. Professional installation included.',
                'description_ar' => 'حل أتمتة كامل للبوابات المنزلقة مع التحكم عن بعد وأجهزة استشعار السلامة. يتضمن التركيب الاحترافي.',
                'brand' => 'SmartFlow',
                'price' => '2500.00',
                'price_number' => 2500.00,
                'image' => null,
                'in_stock' => true,
                'category_id' => 1,
                'features' => [
                    'Quiet operation - صامت جداً',
                    'Automatic safety sensors - مستشعرات أمان',
                    'Remote control included',
                    'Professional installation'
                ],
                'whatsapp_message' => 'مرحباً، أنا مهتم بنظام البوابة المنزلقة الأوتوماتيكية',
            ],
            [
                'name' => 'Smart Home Controller',
                'name_ar' => 'جهاز تحكم ذكي للمنزل',
                'description' => 'Full home automation control system with energy management, voice commands, and mobile app integration.',
                'description_ar' => 'نظام تحكم كامل للمنزل الذكي مع إدارة الطاقة والأوامر الصوتية والتكامل مع تطبيق الهاتف المحمول.',
                'brand' => 'SmartFlow',
                'price' => '1800.00',
                'price_number' => 1800.00,
                'image' => null,
                'in_stock' => true,
                'category_id' => 1,
                'features' => [
                    'Full home automation control',
                    'Energy management system',
                    'Voice command compatible',
                    'Mobile app integration'
                ],
                'whatsapp_message' => 'مرحباً، أنا مهتم بجهاز التحكم الذكي للمنزل',
            ],
            [
                'name' => 'Power Distribution Panel',
                'name_ar' => 'لوحة توزيع كهربائية',
                'description' => 'Smart power distribution panel with circuit protection and real-time monitoring. Safe and certified for residential use.',
                'description_ar' => 'لوحة توزيع كهربائية ذكية مع حماية الدوائر والمراقبة في الوقت الفعلي. آمنة ومعتمدة للاستخدام السكني.',
                'brand' => 'SmartFlow',
                'price' => '2500.00',
                'price_number' => 2500.00,
                'image' => null,
                'in_stock' => true,
                'category_id' => 1,
                'features' => [
                    'Smart power distribution',
                    'Circuit protection system',
                    'Real-time monitoring',
                    'Safe and certified'
                ],
                'whatsapp_message' => 'مرحباً، أنا مهتم بلوحة التوزيع الكهربائية',
            ],
            [
                'name' => 'Energy Control System',
                'name_ar' => 'نظام التحكم بالطاقة',
                'description' => 'Advanced energy control system with consumption monitoring, automated power management, and cloud-based analytics.',
                'description_ar' => 'نظام متقدم للتحكم بالطاقة مع مراقبة الاستهلاك وإدارة الطاقة التلقائية والتحليلات السحابية.',
                'brand' => 'SmartFlow',
                'price' => '4200.00',
                'price_number' => 4200.00,
                'image' => null,
                'in_stock' => true,
                'category_id' => 1,
                'features' => [
                    'Energy consumption monitoring',
                    'Automated power management',
                    'Cost optimization',
                    'Cloud-based analytics'
                ],
                'whatsapp_message' => 'مرحباً، أنا مهتم بنظام التحكم بالطاقة',
            ],
            [
                'name' => 'Smart Lighting System',
                'name_ar' => 'نظام إضاءة ذكي',
                'description' => 'Automated lighting control system with energy-efficient LED, programmable schedules, and remote access.',
                'description_ar' => 'نظام تحكم إضاءة تلقائي مع LED موفر للطاقة وجداول قابلة للبرمجة والوصول عن بُعد.',
                'brand' => 'SmartFlow',
                'price' => '1500.00',
                'price_number' => 1500.00,
                'image' => null,
                'in_stock' => true,
                'category_id' => 1,
                'features' => [
                    'Automated lighting control',
                    'Energy efficient LED',
                    'Programmable schedules',
                    'Remote access'
                ],
                'whatsapp_message' => 'مرحباً، أنا مهتم بنظام الإضاءة الذكي',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
        
        $this->command->info('Products seeded successfully!');
    }
}
