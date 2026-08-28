<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some products to create offers for them
        $products = Product::limit(3)->get();

        foreach ($products as $index => $product) {
            Offer::create([
                'product_id' => $product->id,
                'discount_percentage' => ($index + 1) * 10, // 10%, 20%, 30%
                'discount_price' => null, // Will calculate from percentage
                'offer_description' => 'Limited time offer - ' . $product->name,
                'offer_description_ar' => 'عرض محدود الوقت - ' . $product->name_ar,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'is_active' => true,
            ]);
        }
    }
}

