<?php

namespace Database\Seeders;

use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        if (Quotation::where('number', 'QUE-24-000272')->exists()) {
            return;
        }

        $quotation = Quotation::create([
            'number' => 'QUE-24-000272',
            'date' => '2024-11-05',
            'client_name' => 'Sample Client Co. L.L.C',
            'status' => 'sent',
            'comments' => "Validity: 14 days from estimate date.\nPayment: 50% advance, 50% on completion.",
            'currency' => 'AED',
            'tax_percent' => 5,
            'withholding_tax_percent' => 0,
        ]);

        $items = [
            ['code' => 'SF-CTRL', 'description' => "Smart control panel supply & installation\nIncluding wiring and commissioning", 'quantity' => 1, 'rate' => 8500],
            ['code' => 'SF-SENS', 'description' => 'Flow sensors package (set of 4)', 'quantity' => 1, 'rate' => 2200],
            ['code' => 'SF-LAB', 'description' => 'Site labour & testing', 'quantity' => 3, 'rate' => 450],
        ];

        foreach ($items as $i => $item) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'code' => $item['code'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => round($item['quantity'] * $item['rate'], 2),
                'sort_order' => $i,
            ]);
        }

        $quotation->recalculateTotals();
    }
}
