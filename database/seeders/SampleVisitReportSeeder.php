<?php

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;

/**
 * Sample visit report matching the reference PDF design (SV-2024 style).
 */
class SampleVisitReportSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            '/storage/projects/1773685659_69b84b9b41ce9_12341234.00_00_02_03.Still006.jpg',
            '/storage/projects/1773685659_69b84b9b42299_12341234.00_00_02_12.Still005.jpg',
            '/storage/projects/1773685659_69b84b9b4357a_12341234.00_00_06_09.Still002.jpg',
            '/storage/projects/1773685659_69b84b9b427c2_12341234.00_00_02_24.Still001.jpg',
            '/storage/projects/1773685659_69b84b9b43e2a_12341234.00_00_06_09.Still003.jpg',
        ];

        $executedWorks = implode("\n", [
            'تركيب لوحات التوزيع الرئيسية والفرعية',
            'تمديد الكابلات الكهربائية في جميع الطوابق',
            'تركيب أنظمة الإضاءة والمخارج',
            'تركيب نظام الحماية من الحريق (جزئي)',
            'تركيب نظام الطاقة الشمسية على السطح',
        ]);

        $notes = implode("\n", [
            'سير العمل جيد ومطابق للمخططات في معظم المناطق',
            'ضرورة إنهاء تمديد الكابلات في الطابق الثاني',
            'ملاحظات بسيطة على تركيب بعض الأدوات الكهربائية',
        ]);

        $recommendations = implode("\n", [
            'إكمال الأعمال المتبقية حسب الجدول المعتمد',
            'التأكد من تثبيت الكابلات بشكل محكم ومدعوم',
            'اختبار شامل لجميع الأنظمة قبل التسليم النهائي',
            'تقديم تقرير أسبوعي بالتقدم',
        ]);

        $report = Report::updateOrCreate(
            [
                'title' => 'تقرير زيارة موقع — مثال مرجعي',
                'visit_date' => '2024-05-20',
            ],
            [
                'appointment_slot_id' => null,
                'client_name' => 'مجمع الأعمال – المبنى الإداري',
                'engineer_name' => 'م. أحمد الخالد',
                'visit_time' => '10:00 صباحاً',
                'visit_type' => 'زيارة دورية / متابعة',
                'recipient_entity' => 'مجمع الأعمال – المبنى الإداري',
                'site_address' => 'دبي – منطقة القوز الصناعية',
                'site_company' => 'شركة التدفق الذكي للأنظمة والحلول التقنية',
                'contact_phone' => '+971 50 987 6543',
                'delivery_method' => 'زيارة ميدانية',
                'delivery_notes' => 'تم التنسيق المسبق مع إدارة المرافق',
                'executed_works' => $executedWorks,
                'report_notes' => $notes,
                'recommendations' => $recommendations,
                'content' => $notes,
                'images' => $images,
            ]
        );

        $this->command?->info("Sample visit report seeded (ID: {$report->id}, No: SV-2024-" . str_pad((string) $report->id, 4, '0', STR_PAD_LEFT) . ')');
    }
}
