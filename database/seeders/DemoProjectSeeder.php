<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectContact;
use App\Models\ProjectDeliveryNote;
use App\Models\ProjectExpense;
use App\Models\ProjectPayment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Support\ProjectQr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo CRM project with client, quotation sections, invoice,
 * payments, expenses, and delivery notes.
 */
class DemoProjectSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $customer = Customer::updateOrCreate(
                ['phone' => '+971501234567'],
                [
                    'name' => 'أحمد الراشدي — فيلا جميرا',
                    'email' => 'ahmed.rashidi@example.com',
                    'notes' => 'عميل تجريبي — مشروع كاميرات وفيلا جميرا',
                ]
            );

            $project = Project::updateOrCreate(
                ['title' => 'Jumeirah Villa CCTV & Access'],
                [
                    'title_ar' => 'فيلا جميرا — كاميرات وتحكم دخول',
                    'description' => 'Complete CCTV, access control and networking package for a private villa in Jumeirah.',
                    'description_ar' => 'باقة كاملة للكاميرات، التحكم بالدخول، والشبكة لفيلا خاصة في جميرا. مشروع تجريبي للعرض.',
                    'customer_id' => $customer->id,
                    'location' => 'جميرا، دبي — شارع الشاطئ',
                    'maps_url' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Jumeirah Dubai'),
                    'status' => 'in_progress',
                    'is_public' => true,
                    'is_featured' => true,
                    'order' => 1,
                    'media_type' => 'image',
                ]
            );

            $project->contacts()->delete();
            ProjectContact::create([
                'project_id' => $project->id,
                'name' => 'أحمد الراشدي',
                'phone' => '+971501234567',
                'sort_order' => 0,
            ]);
            ProjectContact::create([
                'project_id' => $project->id,
                'name' => 'المهندس خالد (موقع)',
                'phone' => '+971559876543',
                'sort_order' => 1,
            ]);

            // Prefer real catalog products; invent fallbacks if empty
            $products = Product::query()->orderBy('id')->limit(8)->get();
            if ($products->isEmpty()) {
                $this->command?->warn('No products found — creating demo products.');
                $products = collect([
                    Product::create([
                        'name' => 'IP Camera 4MP',
                        'name_ar' => 'كاميرا IP 4 ميجا',
                        'brand' => 'CAM-4MP',
                        'price' => '450',
                        'description' => 'Outdoor IP camera',
                        'description_ar' => 'كاميرا خارجية شبكية',
                        'is_visible' => true,
                    ]),
                    Product::create([
                        'name' => 'NVR 8CH',
                        'name_ar' => 'جهاز تسجيل 8 قنوات',
                        'brand' => 'NVR-8',
                        'price' => '1200',
                        'description' => 'Network video recorder',
                        'description_ar' => 'مسجل فيديو شبكي',
                        'is_visible' => true,
                    ]),
                    Product::create([
                        'name' => 'Cat6 Cable Box',
                        'name_ar' => 'علبة كيبل Cat6',
                        'brand' => 'CAT6',
                        'price' => '280',
                        'description' => '305m box',
                        'description_ar' => 'علبة 305 متر',
                        'is_visible' => true,
                    ]),
                    Product::create([
                        'name' => 'Access Reader',
                        'name_ar' => 'قارئ بصمة/كارت',
                        'brand' => 'ACS-R1',
                        'price' => '650',
                        'description' => 'Door access reader',
                        'description_ar' => 'قارئ تحكم دخول',
                        'is_visible' => true,
                    ]),
                    Product::create([
                        'name' => 'PoE Switch 8Port',
                        'name_ar' => 'سويتش PoE 8 منافذ',
                        'brand' => 'POE-8',
                        'price' => '520',
                        'description' => 'PoE network switch',
                        'description_ar' => 'سويتش شبكة بالطاقة',
                        'is_visible' => true,
                    ]),
                    Product::create([
                        'name' => 'Installation Labor',
                        'name_ar' => 'أجور تركيب',
                        'brand' => 'LABOR',
                        'price' => '1500',
                        'description' => 'Installation package',
                        'description_ar' => 'باقة تركيب',
                        'is_visible' => true,
                    ]),
                ]);
            }

            $p = fn (int $i) => $products[$i % $products->count()];

            // Remove previous demo quotation/invoice for this project (clean re-seed)
            foreach ($project->quotations as $oldQ) {
                $oldQ->invoices()->delete();
                $oldQ->items()->delete();
                $oldQ->delete();
            }
            $project->payments()->delete();
            $project->expenses()->delete();
            $project->deliveryNotes()->delete();

            $quotation = Quotation::create([
                'number' => 'QUE-26-DEMO01',
                'date' => now()->toDateString(),
                'client_name' => $customer->name,
                'customer_id' => $customer->id,
                'project_id' => $project->id,
                'status' => 'accepted',
                'comments' => "عرض تجريبي — فيلا جميرا\nيشمل التركيب والبرمجة والتدريب.",
                'currency' => 'AED',
                'tax_percent' => 5,
                'withholding_tax_percent' => 0,
            ]);

            $lines = [
                ['section' => 'الكاميرات والتسجيل'],
                ['product' => $p(0), 'qty' => 8, 'rate' => $this->priceOf($p(0), 450)],
                ['product' => $p(1), 'qty' => 1, 'rate' => $this->priceOf($p(1), 1200)],
                ['section' => 'الشبكة والكابلات'],
                ['product' => $p(2), 'qty' => 2, 'rate' => $this->priceOf($p(2), 280)],
                ['product' => $p(4), 'qty' => 1, 'rate' => $this->priceOf($p(4), 520)],
                ['section' => 'التحكم بالدخول'],
                ['product' => $p(3), 'qty' => 2, 'rate' => $this->priceOf($p(3), 650)],
                ['section' => 'التركيب والتشغيل'],
                ['product' => $p(5), 'qty' => 1, 'rate' => $this->priceOf($p(5), 1500)],
            ];

            $sort = 0;
            foreach ($lines as $row) {
                if (isset($row['section'])) {
                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'product_id' => null,
                        'is_section' => true,
                        'code' => null,
                        'description' => $row['section'],
                        'quantity' => 0,
                        'rate' => 0,
                        'amount' => 0,
                        'sort_order' => $sort++,
                    ]);
                    continue;
                }
                $product = $row['product'];
                $qty = (float) $row['qty'];
                $rate = (float) $row['rate'];
                $title = trim((string) ($product->name_ar ?: $product->name));
                $detail = trim((string) ($product->description_ar ?: $product->description ?: ''));
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product->id,
                    'is_section' => false,
                    'code' => $product->brand ?: ('P' . $product->id),
                    'description' => $detail !== '' ? $title . "\n" . $detail : $title,
                    'quantity' => $qty,
                    'rate' => $rate,
                    'amount' => round($qty * $rate, 2),
                    'sort_order' => $sort++,
                ]);
            }

            $quotation->recalculateTotals();
            $quotation->refresh();

            $invoice = Invoice::create([
                'number' => 'INV-26-DEMO01',
                'quotation_id' => $quotation->id,
                'project_id' => $project->id,
                'date' => now()->toDateString(),
                'client_name' => $customer->name,
                'status' => 'sent',
                'notes' => 'فاتورة تجريبية كاملة من عرض QUE-26-DEMO01',
                'currency' => 'AED',
                'amount' => $quotation->total,
                'percent' => 100,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'total' => $quotation->total,
            ]);

            ProjectPayment::create([
                'project_id' => $project->id,
                'amount' => round(((float) $invoice->total) * 0.4, 2),
                'payment_type' => 'bank',
                'paid_at' => now()->subDays(10)->toDateString(),
                'notes' => 'دفعة أولى 40% — تحويل بنكي',
            ]);
            ProjectPayment::create([
                'project_id' => $project->id,
                'amount' => round(((float) $invoice->total) * 0.25, 2),
                'payment_type' => 'cash',
                'paid_at' => now()->subDays(2)->toDateString(),
                'notes' => 'دفعة ثانية كاش عند التركيب',
            ]);

            ProjectExpense::create([
                'project_id' => $project->id,
                'name' => 'كاميرات (شراء جملة)',
                'amount' => 2800,
                'spent_at' => now()->subDays(12)->toDateString(),
                'notes' => 'شراء من المورد',
            ]);
            ProjectExpense::create([
                'project_id' => $project->id,
                'name' => 'وقود',
                'amount' => 180,
                'spent_at' => now()->subDays(5)->toDateString(),
            ]);
            ProjectExpense::create([
                'project_id' => $project->id,
                'name' => 'عمال',
                'amount' => 900,
                'spent_at' => now()->subDays(3)->toDateString(),
                'notes' => 'يومين تركيب',
            ]);
            ProjectExpense::create([
                'project_id' => $project->id,
                'name' => 'أسلاك وكابلات',
                'amount' => 420,
                'spent_at' => now()->subDays(8)->toDateString(),
            ]);

            ProjectDeliveryNote::create([
                'project_id' => $project->id,
                'number' => 'DN-' . $project->id . '-1',
                'title' => 'تسليم الكاميرات والجهاز',
                'delivered_at' => now()->subDays(4)->toDateString(),
                'notes' => 'تم التسليم للموقع واستلام المهندس خالد.',
                'items' => [
                    ['description' => 'كاميرات IP', 'quantity' => 8],
                    ['description' => 'جهاز تسجيل NVR', 'quantity' => 1],
                    ['description' => 'سويتش PoE', 'quantity' => 1],
                ],
            ]);
            ProjectDeliveryNote::create([
                'project_id' => $project->id,
                'number' => 'DN-' . $project->id . '-2',
                'title' => 'تسليم قارئ الدخول',
                'delivered_at' => now()->subDay()->toDateString(),
                'notes' => 'تم تركيب قارئين على الباب الرئيسي والحديقة.',
                'items' => [
                    ['description' => 'قارئ بصمة/كارت', 'quantity' => 2],
                ],
            ]);

            try {
                $qr = ProjectQr::generateAndSave($project->fresh(['customer', 'contacts']));
                if ($qr) {
                    $project->update(['qr_path' => $qr]);
                }
            } catch (\Throwable $e) {
                // QR API may be offline — ignore
            }

            $finance = $project->fresh()->finance_summary;

            $this->command?->info('Demo project ready:');
            $this->command?->info('  Project ID: ' . $project->id . ' — ' . $project->title_ar);
            $this->command?->info('  Customer:   ' . $customer->name);
            $this->command?->info('  Quotation:  ' . $quotation->number . ' = AED ' . number_format((float) $quotation->total, 2));
            $this->command?->info('  Invoice:    ' . $invoice->number);
            $this->command?->info('  Finance:    contract=' . $finance['contract_value']
                . ' expenses=' . $finance['expenses_total']
                . ' profit=' . $finance['profit']
                . ' paid=' . $finance['payments_total']
                . ' due=' . $finance['balance_due']);
            $this->command?->info('  Open: /admin/projects/' . $project->id);
        });
    }

    private function priceOf($product, float $fallback): float
    {
        if (isset($product->price_number) && is_numeric($product->price_number) && (float) $product->price_number > 0) {
            return (float) $product->price_number;
        }
        $raw = preg_replace('/[^\d.]/', '', (string) ($product->price ?? ''));
        $n = (float) $raw;
        return $n > 0 ? $n : $fallback;
    }
}
